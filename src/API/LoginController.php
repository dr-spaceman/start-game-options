<?php

namespace Vgsite\API;

use OutOfBoundsException;
use Respect\Validation\Validator as v;
use Vgsite\API\Controller;
use Vgsite\User;
use Vgsite\API\Exceptions\APIInvalidArgumentException;
use Vgsite\API\Exceptions\APILoginException;
use Vgsite\API\Exceptions\APINotFoundException;
use Vgsite\HTTP\Request;
use Vgsite\UserMapper;
use Vgsite\UserScore;

/**
 * @OA\Schema(schema="user",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="username", type="string"),
 *     @OA\Property(property="password", type="string", description="A hashed string; Only given if explicitly requested using the `fields` parameter."),
 *     @OA\Property(property="email", type="string"),
 *     @OA\Property(property="verified", type="boolean"),
 *     @OA\Property(property="gender", type="string", description="enum('he', 'she', 'them')"),
 *     @OA\Property(property="region", type="string", description="enum('us', 'jp', 'eu', 'au')"),
 *     @OA\Property(property="rank", type="integer"),
 *     @OA\Property(property="avatar", type="string"),
 *     @OA\Property(property="timezone", type="string"),
 *     @OA\Property(property="data_created", type="string", format="date-time"),
 *     @OA\Property(property="data_modified", type="string", format="date-time"),
 *     @OA\Property(property="activity", type="string", format="date-time"),
 *     @OA\Property(property="previous_activity", type="string", format="date-time"),
 *     @OA\Property(property="href", type="string"),
 * )
 */

class LoginController extends Controller
{
    const ALLOWED_METHODS = ['GET', 'POST'];
    const REQUIRED_FIELDS = ['password', 'username'];
    const BASE_URI = API_BASE_URI . '/login';

    protected function getOne($id): void
    {
        $user_mapper = new UserMapper();
        try {
            if (v::email()->validate($id)) {
                $user = $user_mapper->findByEmail($id);
            } else {
                $user = $user_mapper->findByUsername($id);
            }

            $results = [
                ['username' => $user->getUsername()],
            ];

            $this->setPayload($results)->render(200);
        } catch (OutOfBoundsException $e) {
            throw new APINotFoundException($e);
        }
    }

    protected function getAll(): void
    {
        $this->invalidRequestMethod();
    }

    /**
     * @OA\Post(
     *     path="/login/",
     *     description="Login",
     *     operationId="Login",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/user")
     *     ),
     *     @OA\Response(response="200",
     *         description="User modified",
     *         @OA\JsonContent(ref="#/components/schemas/user")
     *     ),
     *     @OA\Response(response="401",
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(response="403",
     *         description="Forbidden",
     *     ),
     *     @OA\Response(response="409",
     *         description="Conflict: Parameter not valid",
     *     ),
     * )
     */
    protected function createFromRequest($body): void
    {
        $input = $this->parseBodyJson($body);

        $user_mapper = new UserMapper();

        try {
            if ($input['username']) {
                $user = $user_mapper->findByUsername($input['username']);
            } elseif ($input['email']) {
                if (!v::email()->validate($input['email'])) {
                    throw new APILoginException("The email address `{$input['email']}` is not valid.", 'email');
                }

                $user = $user_mapper->findByEmail($input['email']);
            } else {
                throw new APILoginException("Parameter `username` or `email` is required", 'username');
            }

            $password = $input['password'];
            if (!$password) {
                throw new APILoginException('Password is required.', 'password');
            }

            $user->verifyPassword($input['password']);

            // Re-hash password if necessary
            $currentHashAlgorithm = PASSWORD_DEFAULT;
            $passwordNeedsRehash = password_needs_rehash($user->getPassword(), $currentHashAlgorithm);
            if ($passwordNeedsRehash === true) {
                // Save new password hash
                $user->setPassword($password, true);
                $user_mapper->save($user);
            }
    
            $_SESSION['user_id'] = $user->getId();
            $_SESSION['user_rank'] = $user->getRank();
            $_SESSION['username'] = $user->getUsername();
            $_SESSION['logged_in'] = 'true';
    
            /**
             * Login complete; Post-login business below
             */

            $user->updateActivity();

            //record current scores and counts
            $score = new UserScore($user);
            $score->save();

            // TODO
            // $user_details = []; //$user_mapper->getAllDetails($user);

            // $badge_mapper = new BadgeMapper();
            // if ($new_badges = $badge_mapper->check) {
            //     $_SESSION['newbadges'] = $new_badges;
            // }

            // //check birthday badge
            // if (substr($user_details['dob'], 5) == date("m-d")) {
            //     $badge_mapper->findById(37)->earn($user);
            // }

            //fb login
            // $check_1 = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM users_oauth WHERE user_id=".$user->getId()." AND oauth_provider='facebook' LIMIT 1"));
            // $check_2 = mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], "SELECT * FROM users_oauth WHERE user_id=".$user->getId()." LIMIT 1"));
            // if (!$fbuser && $check_1) {
            //     require_once $_SERVER['DOCUMENT_ROOT']."/bin/php/fb/src/facebook.php";
            //     $fb = array(
            //       'appId'  => '142628175764082',
            //       'secret' => '5913f988087cecedd1965a3ed6e91eb1'
            //     );
            //     $facebook = new Facebook($fb);
            //     $fbuser = $facebook->getUser();
            //     if ($fbuser) {
            //       try {
            //         // Proceed knowing you have a logged in user who's authenticated.
            //         $fbuser_data = $facebook->api('/me');
            //       } catch (FacebookApiException $e) {
            //         error_log($e);
            //         $fbuser = null;
            //       }
            //     }
            // /*} elseif(!mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q)) && !mysqli_num_rows(mysqli_query($GLOBALS['db']['link'], $q2)) && $_COOKIE['no_oauth'] != "ignore" && $userdat['registered'] != $userdat['activity']){
            //     setcookie("no_oauth", "1", time()+60*60*24*100, "/");*/
            // } elseif (!$check_1 && !$check_2 && $user_details['registered'] != $user_details['activity']) {
            //     $GLOBALS['no_oauth'] = true;
            // }

            $this->render(200);
        } catch (APILoginException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new APILoginException($e->getMessage(), '');
        }
    }

    protected function updateFromRequest($id, $body): void
    {
        $this->invalidRequestMethod();
    }

    protected function delete($id): void
    {
        $this->invalidRequestMethod();
    }
}
