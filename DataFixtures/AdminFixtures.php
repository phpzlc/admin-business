<?php
/**
 * 管理员内置数据
 *
 * Created by Trick
 * user: Trick
 * Date: 2020/12/23
 * Time: 2:36 下午
 */

namespace App\DataFixtures;

use App\Business\AdminBusiness\AdminAuth;
use App\Entity\Admin;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use PHPZlc\PHPZlc\Bundle\Safety\ActionLoad;
use Psr\Container\ContainerInterface;

class AdminFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $admin = new Admin();
        $admin
            ->setName('超级管理员')
            ->setAccount('aitime')
            ->setIsBuilt(true)
            ->setIsSuper(true);

        (new AdminAuth(ActionLoad::$globalContainer))->create($admin, '123456');
    }
}