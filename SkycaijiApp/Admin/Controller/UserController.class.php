<?php
/*
 |--------------------------------------------------------------------------
 | SkyCaiji (蓝天采集器)
 |--------------------------------------------------------------------------
 | Copyright (c) 2018 http://www.skycaiji.com All rights reserved.
 |--------------------------------------------------------------------------
 | 使用协议  http://www.skycaiji.com/licenses
 |--------------------------------------------------------------------------
 */

namespace Admin\Controller; if(!defined('IN_SKYCAIJI')) { exit('NOT IN SKYCAIJI'); } class UserController extends BaseController { public function listAction(){ $muser=D('User'); $page=I('p',1,'intval'); $page=max(1,$page); $limit=20; $count=$muser->count(); $userList=$muser->order('uid asc')->limit($limit)->page($page)->select(); if($count>$limit){ $pageCount=ceil($count/$limit); $cpage = new \Think\Page($count,$limit); $pagenav = bootstrap_pages($cpage->show()); $this->assign('pagenav',$pagenav); } $GLOBALS['content_header']=L('user_list'); $GLOBALS['breadcrumb']=breadcrumb(array(array('url'=>U('User/list'),'title'=>L('user_list')))); $groupList=D('Usergroup')->select(array('index'=>'id')); $this->assign('userList',$userList); $this->assign('groupList',$groupList); $this->display(); } public function addAction(){ $muser=D('User'); $musergroup=D('Usergroup'); if(IS_POST){ if(!check_usertoken()){ $this->error(L('usertoken_error')); } if($GLOBALS['config']['site']['verifycode']){ $verifycode=trim(I('verifycode')); $check=check_verify($verifycode); if(!$check['success']){ $this->error($check['msg']); } } $newData=array( 'username'=>I('username'), 'password'=>I('password'), 'repassword'=>I('repassword'), 'email'=>I('email'), 'groupid'=>I('groupid',0,'intval') ); $check=$muser->add_check($newData); if(!$check['success']){ $this->error($check['msg']); } $newData['password']=pwd_encrypt($newData['password']); $newGroup=$musergroup->getById($newData['groupid']); if($musergroup->user_level_limit($newGroup['level'])){ $this->error('您不能添加“'.$GLOBALS['user']['group']['name'].'”用户组'); } $newData['regtime']=NOW_TIME; $uid=$muser->add($newData); if($uid>0){ $this->success(L('op_success'),U('User/list')); }else{ $this->error(L('op_failed')); } }else{ $subGroupList=$musergroup->get_sub_level($GLOBALS['user']['groupid']); $GLOBALS['content_header']=L('user_add'); $GLOBALS['breadcrumb']=breadcrumb(array(array('url'=>U('User/list'),'title'=>L('user_list')),L('user_add'))); $this->assign('subGroupList',$subGroupList); $this->display(); } } public function editAction(){ $uid=I('uid',0,'intval'); if(empty($uid)){ $this->error(L('user_error_null_uid')); } $muser=D('User'); $musergroup=D('Usergroup'); $userData=$muser->getByUid($uid); if(empty($userData)){ $this->error(L('user_error_empty_user')); } $userData['group']=$musergroup->getById($userData['groupid']); $isOwner=($GLOBALS['user']['uid']==$userData['uid'])?true:false; if(!$isOwner&&$musergroup->user_level_limit($userData['group']['level'])){ $this->error('您不能编辑“'.$userData['group']['name'].'”组的用户'); } if(IS_POST){ if(!check_usertoken()){ $this->error(L('usertoken_error')); } if($GLOBALS['config']['site']['verifycode']){ $verifycode=trim(I('verifycode')); $check=check_verify($verifycode); if(!$check['success']){ $this->error($check['msg']); } } $newData=array( 'password'=>I('password'), 'repassword'=>I('repassword'), 'email'=>I('email'), 'groupid'=>I('groupid',0,'intval') ); if(empty($newData['password'])){ unset($newData['password']); unset($newData['repassword']); } $check=$muser->edit_check($newData); if(!$check['success']){ $this->error($check['msg']); } if(!empty($newData['password'])){ $newData['password']=pwd_encrypt($newData['password']); } $newGroup=$musergroup->getById($newData['groupid']); if($musergroup->user_level_limit($newGroup['level'])){ $this->error('您不能改为“'.$GLOBALS['user']['group']['name'].'”用户组'); } if($isOwner||empty($newData['groupid'])){ unset($newData['groupid']); } $muser->where(array('uid'=>$uid))->save($newData); $this->success(L('op_success'),U('User/list')); }else{ $this->assign('userData',$userData); $subGroupList=$musergroup->get_sub_level($GLOBALS['user']['groupid']); $this->assign('subGroupList',$subGroupList); $this->assign('isOwner',$isOwner); $GLOBALS['content_header']=L('user_edit'); $GLOBALS['breadcrumb']=breadcrumb(array(array('url'=>U('User/list'),'title'=>L('user_list')),L('user_edit'))); $this->display(); } } public function deleteAction(){ $uid=I('uid',0,'intval'); if(empty($uid)){ $this->error(L('user_error_null_uid')); } $muser=D('User'); $musergroup=D('Usergroup'); $userData=$muser->getByUid($uid); if(empty($userData)){ $this->error(L('user_error_empty_user')); } if($userData['uid']==$GLOBALS['user']['uid']){ $this->error('不能删除自己'); } $userData['group']=$musergroup->getById($userData['groupid']); if($musergroup->user_level_limit($userData['group']['level'])){ $this->error('您不能删除“'.$userData['group']['name'].'”组的用户'); } $muser->where(array('uid'=>$uid))->delete(); $this->success(L('op_success'),U('User/list')); } }