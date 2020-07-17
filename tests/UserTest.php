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
    protected static $mapper;

    public static function setUpBeforeClass(): void
    {
        self::$mapper = new UserMapper();
    }

    public function testUserMapperInit()
    {
        $mapper = self::$mapper;
        $this->assertInstanceOf(UserMapper::class, $mapper);
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
        $user = new User(-1, 'test_'.TEST_ID, 'password', 'email');
        $user->setPassword(' invalid_spaces_on_end ');
    }

    public function testUserConfirmInvalidPropSetFails_Rank()
    {
        $this->expectException(InvalidArgumentException::class);
        $user = new User(-1, 'test_'.TEST_ID, 'password', 'email');
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
        $user = new User(-1, TEST_USER_USERNAME, 'password', 'email');
        $user = $mapper->insert($user);
    }

    public function testInsertUserEmailDuplicationFails()
    {
        $mapper = self::$mapper;
        $this->expectException(Exception::class);
        $user = new User(-1, 'foo_123_xyz_fuuuuu', 'password', TEST_USER_EMAIL);
        $user = $mapper->insert($user);
    }

    public function testInsertUser(): User
    {
        $mapper = self::$mapper;
        $user = new User(-1, 'test_'.TEST_ID, 'password', 'test_foobar'.TEST_ID.'@bar.com', User::MEMBER);
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
            array(TEST_USER_ID, TEST_USER_USERNAME, TEST_USER_PASSWORD, TEST_USER_EMAIL),
            array(-1, 'test_'.TEST_ID, 'password', 'email'),
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
        $collection->add(new User(-1, 'TheMadTargaryen', 'password', 'foo@bar.com', User::MEMBER));
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

    public function TestUserDetail()
    {
        
    }

    /**
     * @depends testInsertUser
     */
    public function testDeleteUser($user)
    {
        $this->assertTrue(self::$mapper->delete($user));
    }
}
