<?php
/**
 * 管理端登录授权类
 *
 * Created by Trick
 * user: Trick
 * Date: 2020/12/25
 * Time: 3:48 下午
 */

namespace App\Controller\Admin;

use App\Business\AuthBusiness\AuthTag;
use App\Business\AuthBusiness\UserAuthBusiness;
use App\Business\CaptchaBusiness\CaptchaBusiness;
use PHPZlc\Admin\Strategy\Navigation;
use PHPZlc\PHPZlc\Abnormal\Errors;
use PHPZlc\PHPZlc\Responses\Responses;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends AdminController
{

    /**
     * 管理端登录
     *
     * @param Request $request
     * @return bool|JsonResponse|RedirectResponse
     * @throws \Exception
     */
    public function login(Request $request)
    {
        $r = $this->inlet(self::RETURN_HIDE_RESOURCE, false);
        if($r !== true){
            return $r;
        }

        $userBusiness = new UserAuthBusiness($this->container);

        $account = $request->get('account');
        $password = $request->get('password');

        $ca = new CaptchaBusiness($this->container);

        if($_ENV['ADMIN_ENV'] !== 'dev') {
            if (!$ca->isCaptcha('admin_auth_login', $request->get('imgCode'))) {
                return Responses::error(Errors::getError());
            }
        }

        if($userBusiness->accountLogin($account, $password, $this->getParameter('subject_admin')) === false){
            return Responses::error(Errors::getError());
        }

        return Responses::success('登录成功', ['go_url' => $this->adminStrategy->getEntranceUrl()]);

    }

    /**
     * 管理端退出登录
     *
     * @return false|RedirectResponse
     * @throws \Exception
     */
    public function logout()
    {
        $r = $this->inlet(self::RETURN_SHOW_RESOURCE, true);
        if($r !== true){
            return $r;
        }

        AuthTag::remove($this->container);

        //清除当前登录用户权限缓存
        $this->rbac->clearCache($this->curUserAuth);

        return $this->redirect($this->adminStrategy->getEntranceUrl());
    }

    /**
     * 修改密码
     *
     * @param Request $request
     * @return bool|JsonResponse|RedirectResponse|Response
     * @throws \Exception
     */
    public function editPassword(Request $request)
    {
        $r = $this->inlet($request->getMethod() == 'GET'?self::RETURN_SHOW_RESOURCE:self::RETURN_HIDE_RESOURCE);
        if($r !== true){
            return $r;
        }

        $this->adminStrategy->setNavigations([new Navigation('修改密码')]);

        if($request->getMethod() == 'GET'){
            return $this->render('admin/auth/editPassword.html.twig');
        }else{
            $old_password = $request->get('oldPassword');
            $new_password = $request->get('newPassword');

            $userAuthBusiness = new UserAuthBusiness($this->container);

            if(!$userAuthBusiness->changePassword($this->curUserAuth, $old_password, $new_password)){
                return Responses::error(Errors::getError());
            }else{
                AuthTag::remove($this->container);
                return Responses::success('密码修改成功');
            }
        }
    }

}
