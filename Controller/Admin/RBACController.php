<?php
/**
 * 角色与权限
 *
 * Created by Trick
 * user: Trick
 * Date: 2021/2/23
 * Time: 7:56 上午
 */

namespace App\Controller\Admin;

use App\Business\PlatformBusiness\PlatformClass;
use App\Business\RBACBusiness\PermissionBusiness;
use App\Business\RBACBusiness\RoleBusiness;
use App\Entity\Role;
use App\Entity\Permission;
use App\Repository\PermissionRepository;
use App\Repository\RoleRepository;
use PHPZlc\Admin\Strategy\Navigation;
use PHPZlc\PHPZlc\Abnormal\Errors;
use PHPZlc\PHPZlc\Bundle\Controller\SystemBaseController;
use PHPZlc\PHPZlc\Bundle\Safety\ActionLoad;
use PHPZlc\PHPZlc\Doctrine\ORM\Rule\Rule;
use PHPZlc\PHPZlc\Responses\Responses;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class RBACController extends AdminController
{
    protected $page_tag = 'admin_rbac';

    /**
     * @var RoleBusiness
     */
    private $roleBusiness;

    /**
     * @var RoleRepository
     */
    private $roleRepository;

    /**
     * @var PermissionRepository
     */
    private $permissionRepository;

    public function inlet($returnType = SystemBaseController::RETURN_SHOW_RESOURCE, $isLogin = true)
    {
        $r = parent::inlet($returnType, $isLogin);
        if($r !== true){
            return $r;
        }

        $this->roleBusiness = new RoleBusiness($this->container);
        $this->roleRepository = ActionLoad::$globalDoctrine->getRepository(Role::class);
        $this->permissionRepository = ActionLoad::$globalDoctrine->getRepository(Permission::class);

        $this->adminStrategy->addNavigation(new Navigation('角色与权限'));

        return true;
    }

    /**
     * 角色与权限首页
     *
     * @return bool|JsonResponse|RedirectResponse|Response
     */
    public function roleIndex()
    {
        $r = $this->inlet(self::RETURN_SHOW_RESOURCE);
        if($r !== true){
            return $r;
        }

        $this->adminStrategy->setUrlAnchor();

        $roles = $this->roleRepository->findAll(['platform' => PlatformClass::getPlatform()]);

        return $this->render('admin/rbac/role-index.html.twig', [
            'roles' => $roles
        ]);
    }

    /**
     * 新建/编辑角色
     *
     * @param Request $request
     * @return bool|JsonResponse|RedirectResponse|Response
     */
    public function role(Request $request)
    {
        $r = $this->inlet($request->getMethod() == 'GET' ? self::RETURN_SHOW_RESOURCE : self::RETURN_HIDE_RESOURCE, true);
        if($r !== true){
            return $r;
        }

        $role_id = $request->get('role_id');

        if(empty($role_id)){
            $role = new Role();
        }else{
            $role = $this->roleRepository->findAssocById($role_id);
            if(empty($role)){
                if(self::getReturnType() == self::RETURN_SHOW_RESOURCE){
                    throw new NotFoundHttpException();
                }else{
                    return Responses::error('角色不存在');
                }
            }
        }

        if(self::getReturnType() == self::RETURN_SHOW_RESOURCE){
            if(empty($role_id)){
                $this->adminStrategy->addNavigation(new Navigation('创建角色'));
            }else{
                $this->adminStrategy->addNavigation(new Navigation('编辑角色'));
            }
            return $this->render('admin/rbac/role.html.twig', [
                'role' => $role
            ]);
        }else{
            $role->setName($request->get('name'));
            $role->setTag($request->get('tag'));
            $role->setDataVersion(time());

            if(empty($role_id)){
                $role->setPlatform(PlatformClass::getPlatform());
                if(!$this->roleBusiness->create($role)){
                    return Responses::error(Errors::getError());
                }else{
                    return Responses::success('创建成功');
                }
            }else{
                if(!$this->roleBusiness->update($role)){
                    return Responses::error(Errors::getError());
                }else{
                    return Responses::success('编辑成功');
                }
            }
        }
    }

    /**
     * 角色设置权限页面
     *
     * @param Request $request
     * @return bool|JsonResponse|RedirectResponse|Response
     */
    public function rolePermission(Request $request)
    {
        $r = $this->inlet(self::RETURN_SHOW_RESOURCE);
        if($r !== true){
            return $r;
        }

        $role_id = $request->get('role_id');

        $role = $this->roleRepository->find($role_id);

        if(empty($role)){
            throw new NotFoundHttpException();
        }

        $this->adminStrategy->addNavigation(new Navigation('角色' . $role->getName() . '设置权限'));

        return $this->render('admin/rbac/permission.html.twig', array(
            'role' => $role,
            'roles' => $this->roleRepository->findAll(['platform' => PlatformClass::getPlatform(), Rule::R_WHERE => " AND sql_pre.id <> '{$role->getId()}'"]),
            'permissions' => $this->permissionRepository->findAll(['platform' => PlatformClass::getPlatform()])
        ));
    }

    /**
     * 设置角色权限
     *
     * @param Request $request
     * @return bool|JsonResponse|RedirectResponse
     */
    public function addPermissionToRole(Request $request)
    {
        $r = $this->inlet(self::RETURN_HIDE_RESOURCE);
        if($r !== true){
            return $r;
        }

        $role_id = $request->get('role_id');
        $permission_ids = $request->get('permission_ids');

        foreach ($permission_ids as $permission_id){
            $this->roleBusiness->addPermission($this->roleRepository->find($role_id), $this->permissionRepository->find($permission_id), false);
        }

       ActionLoad::$globalDoctrine->flush();

        return Responses::success('添加成功');
    }

    /**
     * 移除角色权限
     *
     * @param Request $request
     * @return bool|JsonResponse|RedirectResponse
     */
    public function removePermissionToRole(Request $request)
    {
        $r = $this->inlet(self::RETURN_HIDE_RESOURCE);
        if($r !== true){
            return $r;
        }

        $role_id = $request->get('role_id');
        $permission_ids = $request->get('permission_ids');

        foreach ($permission_ids as $permission_id){
            $this->roleBusiness->removePermission($this->roleRepository->find($role_id), $this->permissionRepository->find($permission_id), false);
        }

       ActionLoad::$globalDoctrine->flush();

        return Responses::success('移除成功');
    }

    /**
     * 添加角色
     *
     * @param Request $request
     * @return bool|JsonResponse|RedirectResponse
     */
    public function addRoleToRole(Request $request)
    {
        $r = $this->inlet(self::RETURN_HIDE_RESOURCE);
        if($r !== true){
            return $r;
        }

        $role_id = $request->get('role_id');
        $c_role_ids = $request->get('c_role_ids');

        foreach ($c_role_ids as $c_role_id){
            $this->roleBusiness->addContainRole($this->roleRepository->find($role_id), $this->roleRepository->find($c_role_id), false);
        }

       ActionLoad::$globalDoctrine->flush();

        return Responses::success('添加成功');
    }

    /**
     * 移除角色
     *
     * @param Request $request
     * @return bool|JsonResponse|RedirectResponse
     */
    public function removeRoleToRole(Request $request)
    {
        $r = $this->inlet(self::RETURN_HIDE_RESOURCE);
        if($r !== true){
            return $r;
        }

        $role_id = $request->get('role_id');
        $c_role_ids = $request->get('c_role_ids');

        foreach ($c_role_ids as $c_role_id){
            $this->roleBusiness->removeContainRole($this->roleRepository->find($role_id), $this->roleRepository->find($c_role_id), false);
        }

       ActionLoad::$globalDoctrine->flush();

        return Responses::success('移除成功');
    }

    /**
     * 删除角色
     *
     * @param Request $request
     * @return bool|JsonResponse|RedirectResponse
     */
    public function delRole(Request $request)
    {
        $r = $this->inlet(self::RETURN_HIDE_RESOURCE);
        if($r !== true){
            return $r;
        }

        $role_id = $request->get('role_id');

        $role = $this->roleRepository->findAssocById($role_id);

        if(empty($role_id)){
            return Responses::error('角色不存在');
        }

        if($role->getIsBuilt()){
            return Responses::error('内置数据不允许删除');
        }

        if($this->roleBusiness->del($role)){
            return Responses::success('删除成功');
        }else{
            return Responses::error(Errors::getError());
        }
    }
}
