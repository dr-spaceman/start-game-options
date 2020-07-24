<?php declare(strict_types=1);

require_once dirname(__FILE__) . '/../config/bootstrap_tests.php';

use PHPUnit\Framework\TestCase;
use Vgsite\Collection;
use Vgsite\Registry;
use Vgsite\User;
use Vgsite\UserMapper;
use Vgsite\UserCollection;

class UserTest extends TestCase
{
    /** @var UserMapper */
    protected static $mapper;

    public static function setUpBeforeClass(): void
    {
        self::$mapper = new UserMapper();
    }

    public function testSetupTestUser(): void
    {
        $props = [
            'user_id' => TEST_USER_ID,
            'username' => TEST_USER_USERNAME,
            'email' => TEST_USER_EMAIL,
            'password' => TEST_USER_PASSWORD,
        ];
        $user = new User($props);
        $user->setPassword(TEST_USER_PASSWORD, true);
        $sql = "UPDATE `users` SET `username`=?, `email`=?, `password`=? WHERE `user_id`=? LIMIT 1";
        $statement = Registry::get('pdo')->prepare($sql);
        $this->assertTrue($statement->execute([TEST_USER_USERNAME, TEST_USER_EMAIL, $user->getPassword(), TEST_USER_ID]));
    }

    public function testUserMapperInit()
    {
        $mapper = self::$mapper;
        $this->assertInstanceOf(UserMapper::class, $mapper);
    }

    public function testUserCRUDLifecycle()
    {
        $user = new User([
            'user_id' => -1,
            'username' => 'test_'.uniqid(),
            'email' => uniqid().'@foo.com',
            'password' => 'foo',
        ]);
        $user = self::$mapper->insert($user);
        $this->assertGreaterThanOrEqual(1, $user->getId());

        $user->setUsername('test_' . uniqid());
        $this->assertEquals($user, self::$mapper->save($user));

        $this->assertTrue(self::$mapper->delete($user));
    }

    public function testUserRanksAreWorking()
    {
        $this->assertEquals(User::GUEST, 0);
        $this->assertEquals(User::getRanks()['RESTRICTED'], 1);
        $this->assertEquals(User::getRankName(2), "MEMBER");
    }

    public function testUserRanksThrowExceptionWhenInvalidArgumentGiven()
    {
        $this->expectException(InvalidArgumentException::class);
        User::getRankName(99);
    }

    public function testUserNotFoundWhenGivenWackyUsername()
    {
        $this->expectException(OutOfBoundsException::class);
        $this->assertNull(self::$mapper->findByUsername('invalid_foobar_xyz_123'));
    }

    public function testUserNotFoundWhenGivenWackyID()
    {
        $this->expectException(OutOfBoundsException::class);
        $this->assertNull(self::$mapper->findById(98765887943210));
    }

    public function testUserFindByIdThrowsTypeErrorExceptionWhenNotPassedInteger()
    {
        $this->expectException(TypeError::class);
        self::$mapper->findById('not_an_int');
    }

    public function testFindUser()
    {
        $mapper = self::$mapper;
        $this->assertInstanceOf(UserMapper::class, $mapper);

        $user = $mapper->findById(TEST_USER_ID);
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($user->getEmail(), TEST_USER_EMAIL);

        $user_by_username = $mapper->findByUsername(TEST_USER_USERNAME);
        $this->assertEquals($user_by_username->getId(), TEST_USER_ID);
        $this->assertEquals($user, $user_by_username);

        return $mapper;
    }

    public function testSaveUserAfterPropChangesMade()
    {
        $mapper = self::$mapper;
        $this->assertInstanceOf(UserMapper::class, $mapper);

        $user = $mapper->findById(TEST_USER_ID);
        $this->assertEquals($user->getEmail(), TEST_USER_EMAIL);
        $user->setEmail('foo_xyz_123@bar.com');
        $user->setRank(User::RESTRICTED);
        $mapper->save($user);

        $user = $mapper->findByEmail('foo_xyz_123@bar.com');
        $this->assertEquals(User::RESTRICTED, $user->getRank());
        $user->setRank(User::MEMBER);
        $user->setEmail(TEST_USER_EMAIL);
        $mapper->save($user);
    }

    public function testUserConfirmInvalidPropSetFails_Username()
    {
        $this->expectException(InvalidArgumentException::class);
        $user = self::$mapper->findByEmail(TEST_USER_EMAIL);
        $user->setUsername(' invalid**username ');
    }

    public function testUserConfirmInvalidPropSetFails_Email()
    {
        $mapper = self::$mapper;
        $this->expectException(InvalidArgumentException::class);
        $user = $mapper->findByEmail(TEST_USER_EMAIL);
        $user->setEmail('invalid');
    }

    public function testUserConfirmInvalidPropSetFails_Password()
    {
        $this->expectException(InvalidArgumentException::class);
        $user = new User(['user_id'=>-1, 'username' => 'test_'.TEST_ID, 'email'=>'email', 'password'=>'password']);
        $user->setPassword(' invalid_spaces_on_end ');
    }

    public function testUserConfirmInvalidPropSetFails_Rank()
    {
        $this->expectException(InvalidArgumentException::class);
        $user = new User(['user_id'=>-1, 'username' => 'test_'.TEST_ID, 'email'=>'email', 'password'=>'password']);
        $user->setRank(99999); //Doesn't exist
    }

    public function testUserPasswordHashCanMatchInputPassword()
    {
        $mapper = self::$mapper;
        $user = $mapper->findById(TEST_USER_ID);
        $this->assertTrue(password_verify(TEST_USER_PASSWORD, $user->getPassword()));
    }

    public function testInsertUserUsernameDuplicationFails()
    {
        $mapper = self::$mapper;
        $this->expectException(Exception::class);
        $user = new User(['user_id'=>-1, 'username'=>TEST_USER_USERNAME, 'email'=>'email', 'password'=>'password']);
        $user = $mapper->insert($user);
    }

    public function testInsertUserEmailDuplicationFails()
    {
        $mapper = self::$mapper;
        $this->expectException(Exception::class);
        $user = new User(['user_id'=>-1, 'username'=>'foo_123_xyz_fuuuuu', 'email'=>TEST_USER_EMAIL, 'password'=>'password']);
        $user = $mapper->insert($user);
    }

    public function testInsertUser(): User
    {
        $mapper = self::$mapper;
        $user = new User(['user_id'=>-1, 'username'=>'test_'.TEST_ID, 'password'=>'password', 'email'=>'test_foobar'.TEST_ID.'@bar.com', 'rank'=>User::MEMBER]);
        $this->assertEquals(0, $user->getProp('verified'));
        $user = $mapper->insert($user);
        $this->assertTrue(($user->getId() > -1));

        $this->assertInstanceOf(User::class, $mapper->findById($user->getId()));
        $this->assertInstanceOf(User::class, $mapper->findByUsername('test_'.TEST_ID));
        $this->assertInstanceOf(User::class, $mapper->findByEmail('test_foobar'.TEST_ID.'@bar.com'));

        return $user;
    }

    public function testUserCollectionCanCollectUsersRows()
    {
        $mapper = self::$mapper;
        $rows = array( 
            array('user_id'=>TEST_USER_ID, 'username'=>TEST_USER_USERNAME, 'password'=>TEST_USER_PASSWORD, 'email'=>TEST_USER_EMAIL),
            array('user_id'=>-1, 'username'=>'test_'.TEST_ID, 'email'=>'email@email.com', 'password'=>'password'),
        );
        $collection = new UserCollection($rows, $mapper);
        $this->assertInstanceOf(UserCollection::class, $collection);
        $user_i = $collection->getGenerator();
        $this->assertInstanceOf(Generator::class, $user_i);
        foreach ($user_i as $user) {
            $this->assertInstanceOf(User::class, $user);
        }
    }

    public function testUserCollectionCanCollectUsersObjects()
    {
        $collection = new UserCollection();
        $collection->add(self::$mapper->findById(TEST_USER_ID));
        $collection->add(new User(['user_id'=>-1, 'username'=>'TheMadTargaryen', 'password'=>'password', 'email'=>'foo@bar.com', 'rank'=>User::MEMBER]));
        $this->assertInstanceOf(UserCollection::class, $collection);
        foreach ($collection->getGenerator() as $user) {
            $this->assertInstanceOf(User::class, $user);
        }
    }

    public function testUserFindsAll()
    {
        $mapper = self::$mapper;
        $users = $mapper->findAll();
        $this->assertInstanceOf(Collection::class, $users);
        $this->assertGreaterThanOrEqual(1, $users->count);
    }

    public function testUserFindsAllWithParameters()
    {
        $mapper = self::$mapper;
        $users = $mapper->findAll("email LIKE '%@gmail.com'", 'username', 0, 3);
        $this->assertInstanceOf(Collection::class, $users);
        $this->assertEquals(3, $users->count);
        foreach ($users->getGenerator() as $user) {
            $this->assertInstanceOf(User::class, $user);
            $this->assertStringEndsWith('gmail.com', $user->getEmail());
        }
    }

    /**
     * @depends testInsertUser
     */
    public function testChangeUsername(User $user)
    {
        $sql = "DELETE FROM `users_change_username` WHERE user_id=?";
        $statement = Registry::get('pdo')->prepare($sql);
        $this->assertTrue($statement->execute([$user->getId()]));

        $user->setUsername('test_changed');
        $user = self::$mapper->save($user);

        return $user;
    }

    public function testChangeUsernameFailsWhenChangingToPreviouslyChanged()
    {
        $this->expectException(Exception::class);
        $user = self::$mapper->findById(TEST_USER_ID);
        $user->setUsername('test_changed');
        self::$mapper->save($user);
    }

    /**
     * @depends testChangeUsername
     */
    public function testChangeUsernameFailsOnTooFrequentChange(User $user)
    {
        $this->expectException(Exception::class);
        $user->setUsername('test_'.uniqid());
        self::$mapper->save($user);
    }

    /**
     * @depends testInsertUser
     */
    public function testDeleteUser(User $user)
    {
        $this->assertTrue(($user->getId() > -1));
        $this->assertTrue(self::$mapper->delete($user));

        $this->expectException(OutOfBoundsException::class);
        self::$mapper->findById($user->getId(), false);
    }
}
