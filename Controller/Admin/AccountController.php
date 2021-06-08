<?php
/**
 * PhpStorm.
 * User: Jay
 * Date: 2021/4/22
 */

namespace App\Controller\Admin;


use App\Business\AdminBusiness\AdminAuth;
use App\Business\PlatformBusiness\PlatformClass;
use App\Entity\Admin;
use App\Entity\UserAuthRole;
use PHPZlc\Admin\Strategy\Navigation;
use PHPZlc\PHPZlc\Abnormal\Errors;
use PHPZlc\PHPZlc\Bundle\Controller\SystemBaseController;
use PHPZlc\PHPZlc\Doctrine\ORM\Rule\Rule;
use PHPZlc\PHPZlc\Responses\Responses;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AccountController extends AdminController
{
    /**
     * @var AdminAuth
     */
    private $adminAuthBusiness;

    protected $page_tag = 'admin_account';

    public function inlet($returnType = SystemBaseController::RETURN_SHOW_RESOURCE, $isLogin = true)
    {
        $r = parent::inlet($returnType, $isLogin);
        if($r !== true){
            return $r;
        }

        $this->adminStrategy->addNavigation(new Navigation('账号与角色'));

        $this->adminAuthBusiness = new AdminAuth($this->container);

        return true;
    }

    /**
     * 账号与角色首页
     *
     * @return bool|JsonResponse|RedirectResponse|Response
     */
    public function index()
    {
        $r = $this->inlet(self::RETURN_SHOW_RESOURCE, true);
        if($r !== true){
            return $r;
        }

        $this->adminStrategy->setUrlAnchor();

        $admins = $this->adminRepository->findAll([Rule::R_SELECT => 'sql_pre.* , sql_pre.role_string']);

        return $this->render('admin/account/index.twig', [
            'admins' => $admins
        ]);
    }

    /**
     * 新建/编辑子管理员账号
     *
     * @param Request $request
     * @return bool|JsonResponse|RedirectResponse|Response
     * @throws \Exception
     */
    public function admin(Request $request)
    {
        $r = $this->inlet($request->getMethod() == 'GET' ? self::RETURN_SHOW_RESOURCE : self::RETURN_HIDE_RESOURCE, true);
        if($r !== true){
            return $r;
        }

        $admin_id = $request->get('admin_id');

        if(empty($admin_id)){
            $admin = new Admin();
        }else{
            $admin = $this->adminRepository->findAssocById($admin_id);
            if(empty($admin)){
                if(self::getReturnType() == self::RETURN_SHOW_RESOURCE){
                    throw new NotFoundHttpException();
                }else{
                    return Responses::error('账号不存在');
                }
            }
        }

        if(self::getReturnType() == self::RETURN_SHOW_RESOURCE){
            if(empty($admin_id)){
                $this->adminStrategy->addNavigation(new Navigation('创建账号'));
            }else{
                $this->adminStrategy->addNavigation(new Navigation('编辑账号'));
            }
            return $this->render('admin/account/admin.html.twig', [
                'admin' => $admin
            ]);
        }else{
            $admin->setName($request->get('name'));
            $admin->setAccount($request->get('account'));
            $admin->setIsDisable($request->get('isDisable'));
            $password = $request->get('password');
            $verify_password = $request->get('verify_password');

            if($password != $verify_password){
                return Responses::error('两次密码输入不一致');
            }

            if(empty($admin_id)){
                if(empty($password)){
                    return Responses::error('密码不能为空');
                }
                if(!$this->adminAuthBusiness->create($admin, $password)){
                    return Responses::error(Errors::getError());
                }else{
                    return Responses::success('创建成功');
                }
            }else{
                if(!$this->adminAuthBusiness->update($admin, $password)){
                    return Responses::error(Errors::getError());
                }else{
                    return Responses::success('编辑成功');
                }
            }
        }
    }

    /**
     * 子管理员设置角色页面
     *
     * @param Request $request
     * @return bool|JsonResponse|RedirectResponse|Response
     */
    public function adminRole(Request $request)
    {
        $r = $this->inlet(self::RETURN_SHOW_RESOURCE);
        if($r !== true){
            return $r;
        }

        $admin_id = $request->get('admin_id');

        $admin = $this->adminRepository->find($admin_id);

        if(empty($admin)){
            throw new NotFoundHttpException();
        }

        $this->adminStrategy->addNavigation(new Navigation('管理员' . $admin->getName() . '设置角色'));

        return $this->render('admin/account/role.html.twig', array(
            'admin' => $admin,
            'roles' => $this->getDoctrine()->getRepository('App:Role')->findAll(['platform' => PlatformClass::getPlatform()]),
            'adminRoles' => $this->getDoctrine()->getRepository('App:UserAuthRole')->findAll([
                'role'. Rule::RA_JOIN => [
                    'alias' => 'r'
                ],
                'r.platform' => PlatformClass::getPlatform(),
                'user_auth_id' => $admin->getUserAuth()->getId()
            ])
        ));

    }

    /**
     * 删除子管理员
     *
     * @param Request $request
     * @return bool|JsonResponse|RedirectResponse
     */
    public function delAdmin(Request $request)
    {
        $r = $this->inlet(self::RETURN_HIDE_RESOURCE);
        if($r !== true){
            return $r;
        }

        $admin_id = $request->get('admin_id');

        $admin = $this->adminRepository->findAssocById($admin_id);

        if(empty($admin)){
            return Responses::error('管理员不存在');
        }

        if($admin->getIsBuilt()){
            return Responses::error('内置数据不允许删除');
        }

        if($admin->getId() == $this->curUserAuth->getSubjectId()){
            return Responses::error('不能删除自身');
        }

        if($this->adminAuthBusiness->delete($admin)){
            return Responses::success('删除成功');
        }else{
            return Responses::error(Errors::getError());
        }
    }

    /**
     * 子管理员添加角色
     *
     * @param Request $request
     * @return bool|JsonResponse|RedirectResponse
     */
    public function addRole(Request $request)
    {
        $r = $this->inlet(self::RETURN_HIDE_RESOURCE);
        if($r !== true){
            return $r;
        }

        $admin_user_auth_id = $request->get('admin_user_auth_id');
        $role_ids = $request->get('role_ids');

        foreach ($role_ids as $role_id) {
            $user_auth_role = $this->getDoctrine()->getRepository('App:UserAuthRole')->findAssoc([
                'user_auth_id' => $admin_user_auth_id,
                'role_id' => $role_id
            ]);

            if (empty($user_auth_role)) {
                $userAuth = $this->getDoctrine()->getRepository('App:UserAuth')->find($admin_user_auth_id);
                $role = $this->getDoctrine()->getRepository('App:Role')->find($role_id);

                $userAuthRole = new UserAuthRole();
                $userAuthRole->setRole($role);
                $userAuthRole->setUserAuth($userAuth);

                $this->getDoctrine()->getManager()->persist($userAuthRole);
            }
        }

        $this->getDoctrine()->getManager()->flush();

        return Responses::error('添加成功');
    }

    /**
     * 移除角色
     *
     * @param Request $request
     * @return bool|JsonResponse|RedirectResponse
     */
    public function removeRole(Request $request)
    {
        $r = $this->inlet(self::RETURN_HIDE_RESOURCE);
        if($r !== true){
            return $r;
        }

        $admin_user_auth_id = $request->get('admin_user_auth_id');
        $role_ids = $request->get('role_ids');

        foreach ($role_ids as $role_id) {
            $user_auth_role = $this->getDoctrine()->getRepository('App:UserAuthRole')->findAssoc([
                'user_auth_id' => $admin_user_auth_id,
                'role_id' => $role_id
            ]);

            if (!empty($user_auth_role)) {
                $this->getDoctrine()->getManager()->remove($user_auth_role);
            }

            $this->getDoctrine()->getManager()->flush();
        }

        return Responses::error('移除成功');
    }
}