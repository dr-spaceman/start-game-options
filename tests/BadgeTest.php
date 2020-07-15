<?php declare(strict_types=1);

require_once dirname(__FILE__) . '/../config/bootstrap_tests.php';

use PHPUnit\Framework\TestCase;
use Vgsite\Registry;
use Vgsite\Badge;
use Vgsite\BadgeCollection;
use Vgsite\BadgeMapper;
use Vgsite\User;

class BadgeTest extends TestCase
{

    public function testBadgeStaticMethodsFindBadges()
    {
        $badge = Badge::findById(1);
        $this->assertInstanceOf(Badge::class, $badge);
        $this->assertEquals($badge->name, 'Newbie');
    }

    public function testBadgeStaticFindAll()
    {
        $num_badges = $GLOBALS['pdo']->query("SELECT count(1) FROM badges")->fetchColumn();

        $badges = Badge::findAll();
        $this->assertInstanceOf(BadgeCollection::class, $badges);
        $this->assertEquals($num_badges, $badges->count);
        $badges_gen = $badges->getGenerator();
        $this->assertInstanceOf(\Generator::class, $badges_gen);
        $i = 0;
        foreach ($badges_gen as $badge) {
            $i++;
        }
        $this->assertEquals($num_badges, $i);
    }

    public function testBaggeMapperFindsBadges()
    {
        $mapper = new BadgeMapper();
        $badge = $mapper->findById(1);
        $this->assertInstanceOf(Badge::class, $badge);
        $this->assertEquals($badge->name, 'Newbie');
    }

    public function testBadgeValuesAreWorking()
    {
        $this->assertEquals(0, Badge::GARBAGE);
        $this->assertEquals(1, Badge::getValues()['bronze']);
        $this->assertEquals("silver", Badge::getValueName(2));
    }

    public function testBadgeValuesThrowExceptionWhenInvalidArgumentGiven()
    {
        $this->expectException(InvalidArgumentException::class);
        Badge::getValueName(99);
    }

    public function testBadgeAlreadyEarnedFails()
    {
        $badge_newbie = Badge::findById(1);
        $user = Registry::getMapper('User')->findById(TEST_USER_ID);
        $this->assertFalse($badge_newbie->earn($user));
    }

    public function testBadgeEarnSuccess()
    {
        $pdo = Registry::get('pdo');
        $sql = "DELETE FROM badges_earned WHERE badge_id=1 AND user_id=".TEST_USER_ID;
        $statement = $pdo->query($sql);

        $badge_newbie = Badge::findById(1);
        $user = Registry::getMapper('User')->findById(TEST_USER_ID);
        $this->assertTrue($badge_newbie->earn($user));
    }

    // public function testBadgeNotFoundWhenGivenWackyInfo()
    // {
    //     $this->assertNull(Badge::findByBadgename('invalid_foobar_xyz_123'));
    //     $this->assertNull(Badge::findById(9876543210));
    // }

    // public function testBadgeFindByIdThrowsTypeErrorExceptionWhenNotPassedInteger()
    // {
    //     $this->expectException(TypeError::class);
    //     Badge::findById('not_an_int');
    // }

    // public function testBadgeMapperInit(): BadgeMapper
    // {
    //     $mapper = new BadgeMapper();
    //     $this->assertInstanceOf(BadgeMapper::class, $mapper);

    //     return $mapper;
    // }

    // /**
    //  * @depends testBadgeMapperInit
    //  */
    // public function testBadgeMapperConnectsToDb($mapper)
    // {
    //     $this->assertInstanceOf(BadgeMapper::class, $mapper);

    //     $this->assertInstanceOf(\PDO::class, $mapper->getPdo());
    // }

    // /**
    //  * @depends testBadgeMapperInit
    //  */
    // public function testFindBadge($mapper)
    // {
    //     $this->assertInstanceOf(BadgeMapper::class, $mapper);

    //     $badge = $mapper->findById(TEST_USER_ID);
    //     $this->assertInstanceOf(Badge::class, $badge);
    //     $this->assertEquals($badge->getEmail(), TEST_USER_EMAIL);

    //     $badge_by_username = $mapper->findByBadgename(TEST_USER_USERNAME);
    //     $this->assertEquals($badge_by_username->getId(), TEST_USER_ID);
    //     $this->assertEquals($badge, $badge_by_username);

    //     return $mapper;
    // }

    // /**
    //  * @depends testBadgeMapperInit
    //  */
    // public function testSaveBadgeAfterPropChangesMade($mapper)
    // {
    //     $this->assertInstanceOf(BadgeMapper::class, $mapper);

    //     $badge = $mapper->findById(TEST_USER_ID);
    //     $this->assertEquals($badge->getEmail(), TEST_USER_EMAIL);
    //     $badge->setEmail('foo_xyz_123@bar.com');
    //     $badge->setRank(Badge::RESTRICTED);
    //     $this->assertTrue($mapper->save($badge));

    //     $badge = $mapper->findByEmail('foo_xyz_123@bar.com');
    //     $this->assertEquals(Badge::RESTRICTED, $badge->getRank());
    //     $badge->setRank(Badge::MEMBER);
    //     $badge->setEmail(TEST_USER_EMAIL);
    //     $this->assertTrue($mapper->save($badge));
    // }

    // /**
    //  * @depends testBadgeMapperInit
    //  */
    // public function testBadge_ConfirmInvalidPropSetFails_Email($mapper)
    // {
    //     $this->assertInstanceOf(BadgeMapper::class, $mapper);

    //     $this->expectException(InvalidArgumentException::class);
    //     $badge = $mapper->findByEmail(TEST_USER_EMAIL);
    //     $badge->setEmail('invalid');
    // }

    // /**
    //  * @depends testBadgeMapperInit
    //  */
    // public function testBadge_ConfirmInvalidPropSetFails_Password($mapper)
    // {
    //     $this->assertInstanceOf(BadgeMapper::class, $mapper);

    //     $this->expectException(InvalidArgumentException::class);
    //     $badge = new Badge(-1, 'test_'.TEST_ID, 'password', 'email');
    //     $badge->setPassword(' invalid_spaces_on_end ');
    // }

    // /**
    //  * @depends testBadgeMapperInit
    //  */
    // public function testBadge_ConfirmInvalidPropSetFails_Rank($mapper)
    // {
    //     $this->assertInstanceOf(BadgeMapper::class, $mapper);

    //     $this->expectException(InvalidArgumentException::class);
    //     $badge = new Badge(-1, 'test_'.TEST_ID, 'password', 'email');
    //     $badge->setRank(99999); //Doesn't exist
    // }

    // /**
    //  * @depends testBadgeMapperInit
    //  */
    // public function testBadgePasswordHashCanMatchInputPassword($mapper)
    // {
    //     $badge = $mapper->findById(TEST_USER_ID);
    //     $this->assertTrue(password_verify(TEST_USER_PASSWORD, $badge->getPassword()));
    // }

    // /**
    //  * @depends testBadgeMapperInit
    //  */
    // public function testInsertBadgeBadgenameDuplicationFails($mapper)
    // {
    //     $this->assertInstanceOf(BadgeMapper::class, $mapper);

    //     $this->expectException(Exception::class);
    //     $badge = new Badge(-1, TEST_USER_USERNAME, 'password', 'email');
    //     $mapper->insert($badge);
    // }

    // /**
    //  * @depends testBadgeMapperInit
    //  */
    // public function testInsertBadgeEmailDuplicationFails($mapper)
    // {
    //     $this->assertInstanceOf(BadgeMapper::class, $mapper);

    //     $this->expectException(Exception::class);
    //     $badge = new Badge(-1, 'foo_123_xyz_fuuuuu', 'password', TEST_USER_EMAIL);
    //     $mapper->insert($badge);
    // }

    // /**
    //  * @depends testBadgeMapperInit
    //  */
    // public function testInsertBadge($mapper): Badge
    // {
    //     $this->assertInstanceOf(BadgeMapper::class, $mapper);

    //     $badge = new Badge(-1, 'test_'.TEST_ID, 'password', 'test_foobar'.TEST_ID.'@bar.com', Badge::MEMBER);
    //     $this->assertTrue($mapper->insert($badge));
    //     $this->assertTrue(($badge->getId() > -1));

    //     $this->assertInstanceOf(Badge::class, $mapper->findById($badge->getId()));
    //     $this->assertInstanceOf(Badge::class, $mapper->findByBadgename('test_'.TEST_ID));
    //     $this->assertInstanceOf(Badge::class, $mapper->findByEmail('test_foobar'.TEST_ID.'@bar.com'));

    //     return $badge;
    // }

    // /**
    //  * @depends testBadgeMapperInit
    //  */
    // public function testBadgeCollectionCanCollectBadgesRows($mapper)
    // {
    //     $rows = array( 
    //         array(TEST_USER_ID, TEST_USER_USERNAME, TEST_USER_PASSWORD, TEST_USER_EMAIL),
    //         array(-1, 'test_'.TEST_ID, 'password', 'email'),
    //     );
    //     $collection = new BadgeCollection($rows, $mapper);
    //     $this->assertInstanceOf(BadgeCollection::class, $collection);
    //     $badge_i = $collection->getGenerator();
    //     $this->assertInstanceOf(Generator::class, $badge_i);
    //     foreach ($badge_i as $badge) {
    //         $this->assertInstanceOf(Badge::class, $badge);
    //     }
    // }

    // public function testBadgeCollectionCanCollectBadgesObjects()
    // {
    //     $collection = new BadgeCollection();
    //     $collection->add(Badge::findById(TEST_USER_ID));
    //     $collection->add(new Badge(-1, 'TheMadTargaryen', 'password', 'foo@bar.com', Badge::MEMBER));
    //     $this->assertInstanceOf(BadgeCollection::class, $collection);
    //     foreach ($collection->getGenerator() as $badge) {
    //         $this->assertInstanceOf(Badge::class, $badge);
    //     }
    // }

    // /**
    //  * @depends testBadgeMapperInit
    //  * @depends testInsertBadge
    //  */
    // public function testDeleteBadge($mapper, $badge)
    // {
    //     $this->assertTrue($mapper->delete($badge));
    // }
}
