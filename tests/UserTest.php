<?php declare(strict_types=1);

require_once dirname(__FILE__) . '/../config/bootstrap_tests.php';

use PHPUnit\Framework\TestCase;
use Vgsite\User;
use Vgsite\UserMapper;
use Vgsite\UserCollection;

class UserTest extends TestCase
{
    public function testUserLazyLoaderFindsUsers()
    {
        $user = Vgsite\UserLazyLoader::findById(TEST_USER_ID);
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($user->getEmail(), TEST_USER_EMAIL);
        $this->assertEquals($user->getId(), TEST_USER_ID);
    }

    public function testUserStaticMethodsFindUsers()
    {
        $user = User::findById(TEST_USER_ID);
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($user->getEmail(), TEST_USER_EMAIL);

        $user = User::findByEmail(TEST_USER_EMAIL);
        $this->assertEquals($user->getId(), TEST_USER_ID);
        
        $user = User::findByUsername(TEST_USER_USERNAME);
        $this->assertEquals($user->getId(), TEST_USER_ID);
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

    public function testUserNotFoundWhenGivenWackyInfo()
    {
        $this->assertNull(User::findByUsername('invalid_foobar_xyz_123'));
        $this->assertNull(User::findById(9876543210));
    }

    public function testUserFindByIdThrowsTypeErrorExceptionWhenNotPassedInteger()
    {
        $this->expectException(TypeError::class);
        User::findById('not_an_int');
    }

    public function testUserMapperInit(): UserMapper
    {
        $mapper = new UserMapper();
        $this->assertInstanceOf(UserMapper::class, $mapper);

        return $mapper;
    }

    /**
     * @depends testUserMapperInit
     */
    public function testUserMapperConnectsToDb($mapper)
    {
        $this->assertInstanceOf(UserMapper::class, $mapper);

        $this->assertInstanceOf(\PDO::class, $mapper->getPdo());
    }

    /**
     * @depends testUserMapperInit
     */
    public function testFindUser($mapper)
    {
        $this->assertInstanceOf(UserMapper::class, $mapper);

        $user = $mapper->findById(TEST_USER_ID);
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($user->getEmail(), TEST_USER_EMAIL);

        $user_by_username = $mapper->findByUsername(TEST_USER_USERNAME);
        $this->assertEquals($user_by_username->getId(), TEST_USER_ID);
        $this->assertEquals($user, $user_by_username);

        return $mapper;
    }

    /**
     * @depends testUserMapperInit
     */
    public function testSaveUserAfterPropChangesMade($mapper)
    {
        $this->assertInstanceOf(UserMapper::class, $mapper);

        $user = $mapper->findById(TEST_USER_ID);
        $this->assertEquals($user->getEmail(), TEST_USER_EMAIL);
        $user->setEmail('foo_xyz_123@bar.com');
        $user->setRank(User::RESTRICTED);
        $this->assertTrue($mapper->save($user));

        $user = $mapper->findByEmail('foo_xyz_123@bar.com');
        $this->assertEquals(User::RESTRICTED, $user->getRank());
        $user->setRank(User::MEMBER);
        $user->setEmail(TEST_USER_EMAIL);
        $this->assertTrue($mapper->save($user));
    }

    /**
     * @depends testUserMapperInit
     */
    public function testUser_ConfirmInvalidPropSetFails_Email($mapper)
    {
        $this->assertInstanceOf(UserMapper::class, $mapper);

        $this->expectException(InvalidArgumentException::class);
        $user = $mapper->findByEmail(TEST_USER_EMAIL);
        $user->setEmail('invalid');
    }

    /**
     * @depends testUserMapperInit
     */
    public function testUser_ConfirmInvalidPropSetFails_Password($mapper)
    {
        $this->assertInstanceOf(UserMapper::class, $mapper);

        $this->expectException(InvalidArgumentException::class);
        $user = new User(-1, 'test_'.TEST_ID, 'password', 'email');
        $user->setPassword(' invalid_spaces_on_end ');
    }

    /**
     * @depends testUserMapperInit
     */
    public function testUser_ConfirmInvalidPropSetFails_Rank($mapper)
    {
        $this->assertInstanceOf(UserMapper::class, $mapper);

        $this->expectException(InvalidArgumentException::class);
        $user = new User(-1, 'test_'.TEST_ID, 'password', 'email');
        $user->setRank(99999); //Doesn't exist
    }

    /**
     * @depends testUserMapperInit
     */
    public function testUserPasswordHashCanMatchInputPassword($mapper)
    {
        $user = $mapper->findById(TEST_USER_ID);
        $this->assertTrue(password_verify(TEST_USER_PASSWORD, $user->getPassword()));
    }

    /**
     * @depends testUserMapperInit
     */
    public function testInsertUserUsernameDuplicationFails($mapper)
    {
        $this->assertInstanceOf(UserMapper::class, $mapper);

        $this->expectException(Exception::class);
        $user = new User(-1, TEST_USER_USERNAME, 'password', 'email');
        $mapper->insert($user);
    }

    /**
     * @depends testUserMapperInit
     */
    public function testInsertUserEmailDuplicationFails($mapper)
    {
        $this->assertInstanceOf(UserMapper::class, $mapper);

        $this->expectException(Exception::class);
        $user = new User(-1, 'foo_123_xyz_fuuuuu', 'password', TEST_USER_EMAIL);
        $mapper->insert($user);
    }

    /**
     * @depends testUserMapperInit
     */
    public function testInsertUser($mapper): User
    {
        $this->assertInstanceOf(UserMapper::class, $mapper);

        $user = new User(-1, 'test_'.TEST_ID, 'password', 'test_foobar'.TEST_ID.'@bar.com', User::MEMBER);
        $this->assertTrue($mapper->insert($user));
        $this->assertTrue(($user->getId() > -1));

        $this->assertInstanceOf(User::class, $mapper->findById($user->getId()));
        $this->assertInstanceOf(User::class, $mapper->findByUsername('test_'.TEST_ID));
        $this->assertInstanceOf(User::class, $mapper->findByEmail('test_foobar'.TEST_ID.'@bar.com'));

        return $user;
    }

    /**
     * @depends testUserMapperInit
     */
    public function testUserCollectionCanCollectUsersRows($mapper)
    {
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
        $collection->add(User::findById(TEST_USER_ID));
        $collection->add(new User(-1, 'TheMadTargaryen', 'password', 'foo@bar.com', User::MEMBER));
        $this->assertInstanceOf(UserCollection::class, $collection);
        foreach ($collection->getGenerator() as $user) {
            $this->assertInstanceOf(User::class, $user);
        }
    }

    /**
     * @depends testUserMapperInit
     * @depends testInsertUser
     */
    public function testDeleteUser($mapper, $user)
    {
        $this->assertTrue($mapper->delete($user));
    }
}
