<?php
/**
 * 管理平台基础类
 *
 * Created by Trick
 * user: Trick
 * Date: 2020/12/25
 * Time: 2:48 下午
 */

namespace App\Controller\Admin;

use App\Business\AuthBusiness\CurAuthSubject;
use App\Business\AuthBusiness\UserAuthBusiness;
use App\Business\PlatformBusiness\PlatformClass;
use App\Entity\UserAuth;
use App\Repository\AdminRepository;
use PHPZlc\Admin\Strategy\AdminStrategy;
use PHPZlc\Admin\Strategy\Menu;
use PHPZlc\PHPZlc\Abnormal\Errors;
use PHPZlc\PHPZlc\Bundle\Controller\SystemBaseController;
use PHPZlc\PHPZlc\Responses\Responses;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class AdminManageController extends SystemBaseController
{
    /**
     * 管理策略
     *
     * @var AdminStrategy
     */
    protected $adminStrategy;

    /**
     * 管理员信息表
     *
     * @var AdminRepository
     */
    protected $adminRepository;

    /**
     * 当前登录管理员授权信息
     *
     * @var UserAuth
     */
    protected $curUserAuth;

    /**
     * 页面标记
     *
     * @var string
     */
    protected $page_tag;

    public function inlet($returnType = SystemBaseController::RETURN_HIDE_RESOURCE, $isLogin = true)
    {
        PlatformClass::setPlatform($this->getParameter('platform_admin'));

        $this->adminRepository = $this->getDoctrine()->getRepository('App:Admin');

        //菜单
        $menus = [
            new Menu('PHPZlc', null, null, null, null, [
                new Menu('PHPZlc首页', null, null, null, null)
            ])
        ];

        $this->adminStrategy = new AdminStrategy($this->container);

        //设置管理端基本信息(名称,页面标记,菜单......)
        $this->adminStrategy
            ->setTitle('PHPZlc')
            ->setEntranceUrl($this->generateUrl('admin_manage_index'))
            ->setEndUrl($this->generateUrl('admin_manage_logout'))
            ->setSettingPwdUrl($this->generateUrl('admin_manage_edit_password'))
            ->setMenuModel(AdminStrategy::menu_model_simple)
            ->setPageTag($this->page_tag)
            ->setMenus($menus);

        $r = parent::inlet($returnType, $isLogin);

        if($r !== true){
            return $r;
        }

        if($isLogin){
            $userAuthBusiness = new UserAuthBusiness($this->container);
            $userAuth = $userAuthBusiness->isLogin();

            $this->curUserAuth = $userAuth;

            if($userAuth === false){
                if(self::getReturnType() === SystemBaseController::RETURN_HIDE_RESOURCE){
                    return Responses::error(Errors::getError(), -1, ['go_url' => $this->adminStrategy->getEntranceUrl()]);
                }else{
                    return $this->redirect($this->adminStrategy->getEntranceUrl());
                }
            }

            $this->adminStrategy->setAdminName(CurAuthSubject::getCurUser()->getAccount());
            $this->adminStrategy->setAdminRoleName(CurAuthSubject::getCurUser()->getName());


            //对路由进行权限校验


            //对菜单进行权限筛选
        }

        return true;

    }

    /**
     * 管理端首页
     *
     * @return RedirectResponse|Response
     */
    public function index()
    {
        $r = $this->inlet(SystemBaseController::RETURN_SHOW_RESOURCE, true);

        if($r === true){
            if(!empty(CurAuthSubject::getCurAuthSuccessGoUrl())){
                return $this->redirect(CurAuthSubject::getCurAuthSuccessGoUrl());
            }
            return $this->render('admin/index.html.twig');
        }

        return $this->render('admin/Auth/login.html.twig');
    }

}