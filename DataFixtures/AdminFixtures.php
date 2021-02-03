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
use Psr\Container\ContainerInterface;

class AdminFixtures extends Fixture
{
    private $container;
    
    public function __construct(ContainerInterface $container = null )
    {
        $this->container = $container;
    }
    
    public function load(ObjectManager $manager)
    {
        $admin = new Admin();
        $admin
            ->setName('超级管理员')
            ->setAccount('aitime')
            ->setIsBuilt(true)
            ->setIsSuper(true);

        
        (new AdminAuth($this->container))->create($admin, '123456');
    }
}