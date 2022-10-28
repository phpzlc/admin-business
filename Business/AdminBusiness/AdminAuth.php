<?php
/**
 * 管理员业务层
 *
 * Created by Trick
 * user: Trick
 * Date: 2020/12/21
 * Time: 4:44 下午
 */

namespace App\Business\AdminBusiness;

use App\Business\AuthBusiness\SubjectAuthInterface;
use App\Business\AuthBusiness\UserAuthBusiness;
use App\Entity\Admin;
use App\Entity\UserAuth;
use App\Repository\AdminRepository;
use PHPZlc\PHPZlc\Abnormal\Errors;
use PHPZlc\PHPZlc\Bundle\Business\AbstractBusiness;
use Psr\Container\ContainerInterface;
use Exception;

class AdminAuth extends AbstractBusiness implements SubjectAuthInterface
{
    /**
     * @var AdminRepository
     */
    public $adminRepository;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->adminRepository = $this->getDoctrine()->getRepository(Admin::class);
    }

    /**
     * 验证器
     *
     * @param $class
     * @return bool
     */
    public function validator($class): bool
    {
        if(parent::validator($class)){
            if($class instanceof Admin){
                $admin = $this->adminRepository->findAssoc(['account' => $class->getAccount()]);
                if(!empty($admin) && $admin->getId() != $class->getId()){
                    Errors::setErrorMessage('管理员账号已被使用');
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * 获取单个管理员信息
     *
     * @param $rules
     * @return Admin[]|mixed|object[]
     */
    public function user($rules)
    {
        return $this->adminRepository->findOneBy($rules);
    }

    /**
     * 检查管理员账号状态
     *
     * @param $user
     * @return bool|mixed
     */
    public function checkStatus($user)
    {
        if($user->getisDisable()){
            Errors::setErrorMessage('账号已被禁用,请联系管理员');
            return false;
        }

        if($user->getisDel()){
            Errors::setErrorMessage('账号已被删除,请联系管理员');
            return false;
        }

        return true;
    }

    /**
     * 创建管理员
     *
     * @param Admin $admin
     * @param $password
     * @return bool
     * @throws Exception
     */
    public function create(Admin $admin, $password)
    {
        $admin->setCreateAt(new \DateTime());

        if(!$this->validator($admin)){
            return false;
        }

        try {
            $this->em->persist($admin);


            $userAuth = new UserAuth();
            $userAuthBusiness = new UserAuthBusiness($this->container);

            $salt = $userAuthBusiness->generateSalt(4);

            $userAuth->setSubjectType('admin');
            $userAuth->setSalt($salt);
            $userAuth->setPassword($password);
            $userAuth->setSubjectId($admin->getId());

            $admin->setUserAuth($userAuth);

            if(!$userAuthBusiness->create($userAuth)){
                throw new \Exception();
            }

            return true;

        }catch (\Exception $exception){
            $this->networkError($exception);
            return false;
        }
    }

    /**
     * 更新管理员
     *
     * @param Admin $admin
     * @param null $password
     * @return bool
     */
    public function update(Admin $admin, $password = null)
    {
        $admin->setUpdateAt(new \DateTime());

        if(!$this->validator($admin)){
            return false;
        }

        if(!empty($password)){
            $userAuthBusiness = new UserAuthBusiness($this->container);
        }

        $this->em->flush();
        $this->em->clear();

        return true;
    }

    /**
     * 删除管理员
     *
     * @param Admin $admin
     * @return bool
     */
    public function delete(Admin $admin)
    {
        $admin->setIsDel(true);
        $admin->setUpdateAt(new \DateTime());

        $this->em->flush();
        $this->em->clear();

        return true;
    }

    /**
     * 管理员入口权限
     *
     * @param UserAuth $userAuth
     */
    public function inletSet(UserAuth $userAuth)
    {

    }

}
