<?
class punbb
{
function __construct($patch)
    {
    IF(!is_file($patch.'include/common.php'))
        {
        new Exception('Wrong $patch, no punBB files');
        }
    $this->patch = $patch;
    echo $this->patch;
    include_once $patch.'config.php';
    $this->db_prefix = $db_prefix;
    }
function set_define()
    {
    return 'define(\'PUN_ROOT\', \''.$_SERVER["DOCUMENT_ROOT"].'/forum/\');
    include_once PUN_ROOT.\'include/common.php\';';
    }
function set_variables($user, $config)
    {
    $this->pun_user = $user;
    $this->pun_config = $config;
    }
function check_login()
    {
    IF($this->pun_user['group_id'] == 3)    return false;
    IF($this->pun_user['username'] == 'Guest')    return false;
    IF($this->pun_user['password'] == 'Guest')    return false;
    IF($this->pun_user['id'] == 1)    return false;
    return true;
    }
function logout()
    {
    punbb::query('DELETE FROM '.$this->db_prefix.'online WHERE user_id='.$this->pun_user['id']);
    pun_setcookie(1, random_pass(8), time() + 31536000);
    }
function login_form()
    {
    ob_start();
    echo '<form id="login" method="post" action="/forum/login.php?action=in" onsubmit="return process_form(this)">
    <input type="hidden" name="form_sent" value="1" />
    <input type="hidden" name="redirect_url" value="'.$_SERVER['SCRIPT_NAME'].'" />
    Username: <input type="text" name="req_username" size="15" maxlength="25" /> 
    Password: <input type="password" name="req_password" size="15" maxlength="16" /> 
    <input type="submit" name="login" value="Login" />
    </form>';
    $module = ob_get_contents();
    ob_end_clean();
    return $module;
    }
function login($login, $pass)
    {
    global $db;
    $form_username = trim($login);
    $form_password = trim($pass);

    $username_sql = ($db_type == 'mysql' || $db_type == 'mysqli') ? 'username=\''.$db->escape($form_username).'\'' : 'LOWER(username)=LOWER(\''.$db->escape($form_username).'\')';

    $result = $db->query('SELECT id, group_id, password, save_pass FROM '.$db->prefix.'users WHERE '.$username_sql) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
    list($user_id, $group_id, $db_password_hash, $save_pass) = $db->fetch_row($result);

    $authorized = false;

    if (!empty($db_password_hash))
    {
        $sha1_in_db = (strlen($db_password_hash) == 40) ? true : false;
        $sha1_available = (function_exists('sha1') || function_exists('mhash')) ? true : false;

        $form_password_hash = pun_hash($form_password);    // This could result in either an SHA-1 or an MD5 hash (depends on $sha1_available)

        if ($sha1_in_db && $sha1_available && $db_password_hash == $form_password_hash)
            $authorized = true;
        else if (!$sha1_in_db && $db_password_hash == md5($form_password))
        {
            $authorized = true;

            if ($sha1_available)    // There's an MD5 hash in the database, but SHA1 hashing is available, so we update the DB
                $db->query('UPDATE '.$db->prefix.'users SET password=\''.$form_password_hash.'\' WHERE id='.$user_id) or error('Unable to update user password', __FILE__, __LINE__, $db->error());
        }
    }

    if (!$authorized)
        message($lang_login['Wrong user/pass'].' <a href="login.php?action=forget">'.$lang_login['Forgotten pass'].'</a>');

    // Update the status if this is the first time the user logged in
    if ($group_id == PUN_UNVERIFIED)
        $db->query('UPDATE '.$db->prefix.'users SET group_id='.$pun_config['o_default_user_group'].' WHERE id='.$user_id) or error('Unable to update user status', __FILE__, __LINE__, $db->error());

    // Remove this users guest entry from the online list
    $db->query('DELETE FROM '.$db->prefix.'online WHERE ident=\''.$db->escape(get_remote_address()).'\'') or error('Unable to delete from online list', __FILE__, __LINE__, $db->error());

    $expire = ($save_pass == '1') ? time() + 31536000 : 0;
    IF(pun_setcookie($user_id, $form_password_hash, $expire))
        {
        return true;
        }
    }
function register($login, $pass, $email)
    {
    global $db;
    $username = pun_trim($login);
    $email1 = strtolower(trim($email));

    if ($this->pun_config['o_regs_verify'] == '1')
    {
        $email2 = strtolower(trim($email));

        $password1 = random_pass(8);
        $password2 = $password1;
    }
    else
    {
        $password1 = trim($pass);
        $password2 = trim($pass);
    }

    // Convert multiple whitespace characters into one (to prevent people from registering with indistinguishable usernames)
    $username = preg_replace('#\s+#s', ' ', $username);

    // Validate username and passwords
    if (strlen($username) < 2)
        message($lang_prof_reg['Username too short']);
    else if (pun_strlen($username) > 25)    // This usually doesn't happen since the form element only accepts 25 characters
        message($lang_common['Bad request']);
    else if (strlen($password1) < 4)
        message($lang_prof_reg['Pass too short']);
    else if ($password1 != $password2)
        message($lang_prof_reg['Pass not match']);
    else if (!strcasecmp($username, 'Guest') || !strcasecmp($username, $lang_common['Guest']))
        message($lang_prof_reg['Username guest']);
    else if (preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $username))
        message($lang_prof_reg['Username IP']);
    else if ((strpos($username, '[') !== false || strpos($username, ']') !== false) && strpos($username, '\'') !== false && strpos($username, '"') !== false)
        message($lang_prof_reg['Username reserved chars']);
    else if (preg_match('#\[b\]|\[/b\]|\[u\]|\[/u\]|\[i\]|\[/i\]|\[color|\[/color\]|\[quote\]|\[quote=|\[/quote\]|\[code\]|\[/code\]|\[img\]|\[/img\]|\[url|\[/url\]|\[email|\[/email\]#i', $username))
        message($lang_prof_reg['Username BBCode']);

    // Check username for any censored words
    if ($this->pun_config['o_censoring'] == '1')
    {
        // If the censored username differs from the username
        if (censor_words($username) != $username)
            message($lang_register['Username censor']);
    }

    // Check that the username (or a too similar username) is not already registered
    $result = $db->query('SELECT username FROM '.$db->prefix.'users WHERE UPPER(username)=UPPER(\''.$db->escape($username).'\') OR UPPER(username)=UPPER(\''.$db->escape(preg_replace('/[^\w]/', '', $username)).'\')') or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());

    if ($db->num_rows($result))
    {
        $busy = $db->result($result);
        message($lang_register['Username dupe 1'].' '.pun_htmlspecialchars($busy).'. '.$lang_register['Username dupe 2']);
    }


    
    $banned_email = false;

    // Check if someone else already has registered with that e-mail address
    $dupe_list = array();

    $result = $db->query('SELECT username FROM '.$db->prefix.'users WHERE email=\''.$email1.'\'') or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
    if ($db->num_rows($result))
    {
        if ($this->pun_config['p_allow_dupe_email'] == '0')
            message($lang_prof_reg['Dupe e-mail']);

        while ($cur_dupe = $db->fetch_assoc($result))
            $dupe_list[] = $cur_dupe['username'];
    }

    $timezone = intval($_POST['timezone']);
    $language = isset($_POST['language']) ? $_POST['language'] : $this->pun_config['o_default_lang'];
    $save_pass = (!isset($_POST['save_pass']) || $_POST['save_pass'] != '1') ? '0' : '1';

    $email_setting = intval($_POST['email_setting']);
    if ($email_setting < 0 || $email_setting > 2) $email_setting = 1;

    // Insert the new user into the database. We do this now to get the last inserted id for later use.
    $now = time();

    $intial_group_id = ($this->pun_config['o_regs_verify'] == '0') ? $this->pun_config['o_default_user_group'] : PUN_UNVERIFIED;
    $password_hash = pun_hash($password1);

    // Add the user
    $db->query('INSERT INTO '.$db->prefix.'users (username, group_id, password, email, email_setting, save_pass, timezone, language, style, registered, registration_ip, last_visit) VALUES(\''.$db->escape($username).'\', '.$intial_group_id.', \''.$password_hash.'\', \''.$email1.'\', '.$email_setting.', '.$save_pass.', '.$timezone.' , \''.$db->escape($language).'\', \''.$this->pun_config['o_default_style'].'\', '.$now.', \''.get_remote_address().'\', '.$now.')') or error('Unable to create user', __FILE__, __LINE__, $db->error());
    $new_uid = $db->insert_id();

    pun_setcookie($new_uid, $password_hash, ($save_pass != '0') ? $now + 31536000 : 0);
    }
function is_admin()
    {
    IF($this->pun_user['group_id'] == 1)
        {
        return true;
        }
    else
        {
        return false;
        }
    }
function is_user()
    {
    IF($this->pun_user['group_id'] == 4)
        {
        return true;
        }
    else
        {
        return false;
        }
    }
################################
function query($query)
    {
    global $db;
    IF($this->db_type == 'pgsql' or $this->db_type == 'sqlite')
        {
        IF(!$db->start_transaction())
            {
            die('Błąd tranzakcji');
            }
        }
    elseIF(ereg('SELECT', $query))
        {
        IF($make = $db->query($query))
            {
            while ($row = $db->fetch_assoc($make))
                {
                $return[] = $row;
                }
            }
        else
            {
                echo '<pre>'.$query;
                print_r($db->error());
                exit();
            }
        IF($this->db_type == 'pgsql' or $this->db_type == 'sqlite')
            {
            $db->end_transaction();
            }
        return $return;
        }
    else
        {
        IF(!$db->query($query))
            {
                echo '<pre>'.$query;
                print_r($db->error());
                IF($this->db_type == 'pgsql' or $this->db_type == 'sqlite')
                    {
                    $db->end_transaction();
                    }
                exit();
            }
        else
            {
            IF($this->db_type == 'pgsql' or $this->db_type == 'sqlite')
                {
                $db->end_transaction();
                }
            return true;
            }
        }
    }
}
?>