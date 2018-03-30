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

namespace Admin\Controller; use Think\Controller; if(!defined('IN_SKYCAIJI')) { exit('NOT IN SKYCAIJI'); } class CollectorController extends BaseController { public function indexAction(){ $this->display(); } public function setAction(){ $taskId=I('task_id',0,'intval'); $mtask=D('Task'); $mcoll=D('Collector'); $taskData=$mtask->getById($taskId); if(empty($taskData)){ $this->error(L('task_error_empty_task')); } if(empty($taskData['module'])){ $this->error(L('task_error_null_module')); } if(!in_array($taskData['module'],C('ALLOW_COLL_MODULES'))){ $this->error(L('coll_error_invalid_module')); } $collData=$mcoll->where(array('task_id'=>$taskData['id'],'module'=>$taskData['module']))->find(); if(IS_POST){ $effective=I('effective'); if(empty($effective)){ $this->error(L('coll_error_empty_effective')); } $name=trim(I('name')); $module=trim(I('module')); if(!in_array($module,C('ALLOW_COLL_MODULES'))){ $this->error(L('coll_error_invalid_module')); } $config=I('post.config/a',null,'trim'); $config=array_array_map('trim',$config); $acoll=a_c($module); $config=$acoll->setConfig($config); $newColl=array('name'=>$name,'module'=>$module,'task_id'=>$taskId,'config'=>serialize($config),'uptime'=>NOW_TIME); $collId=$collData['id']; if(empty($collData)){ $collId=$mcoll->add_new($newColl); }else{ $mcoll->edit_by_id($collId,$newColl); } if($collId>0){ $tab_link=trim(I('tab_link'),'#'); $this->success(L('op_success'),U('Collector/set?task_id='.$taskId.($tab_link?'&tab_link='.$tab_link:''))); }else{ $this->error(L('op_failed')); } }else{ if(!empty($collData)){ $collData['config']=unserialize($collData['config']); } $GLOBALS['content_header']=L('coll_set').L('separator').L('task_module_'.$taskData['module']); $GLOBALS['breadcrumb']=breadcrumb(array(array('url'=>U('Task/edit?id='.$taskData['id']),'title'=>L('task').L('separator').$taskData['name']),L('coll_set'))); $this->assign('collData',$collData); $this->assign('taskData',$taskData); $this->display(); } } public function testAction(){ set_time_limit(600); $coll_id=I('coll_id',0,'intval'); $mcoll=D('Collector'); $collData=$mcoll->where(array('id'=>$coll_id))->find(); if(empty($collData)){ $this->error(L('coll_error_empty_coll')); } if(!in_array($collData['module'],C('ALLOW_COLL_MODULES'))){ $this->error(L('coll_error_invalid_module')); } $this->assign('collData',$collData); $acoll=a_c($collData['module']); $acoll->init($collData); $acoll->test(); } public function listAction(){ $page=max(1,I('p',0,'intval')); $module=I('module'); $pageParams=array(); $cond=array(); $taskCond=array(); if(!empty($module)){ $cond=array('module'=>$module); $pageParams['module']=$module; } $mcoll=D('Collector'); $limit=20; $count=$mcoll->where($cond)->count(); $collList=$mcoll->where($cond)->limit($limit)->page($page)->select(); if($count>0){ $taskIds=array(); foreach ($collList as $coll){ $taskIds[$coll['task_id']]=$coll['task_id']; } if(!empty($taskIds)){ $taskCond['id']=array('in',$taskIds); $taskNames=D('Task')->where($taskCond)->select(array('index'=>'id,name')); $this->assign('taskNames',$taskNames); } if($count>$limit){ $pageCount=ceil($count/$limit); $cpage = new \Think\Page($count,$limit); if(!empty($pageParams)){ $cpage->parameter=array_merge($cpage->parameter,$pageParams); } $pagenav = bootstrap_pages($cpage->show()); $this->assign('pagenav',$pagenav); } } $this->assign('collList',$collList); $this->display('list'.($_GET['tpl']?'_'.$_GET['tpl']:'')); } public function save2storeAction(){ $coll_id=I('coll_id',0,'intval'); $mcoll=D('Collector'); $collData=$mcoll->where(array('id'=>$coll_id))->find(); if(empty($collData)){ $this->error(L('coll_error_empty_coll')); } if(!in_array($collData['module'],C('ALLOW_COLL_MODULES'))){ $this->error(L('coll_error_invalid_module')); } $config=unserialize($collData['config']); if(empty($config)){ $this->error('规则不存在'); } if(empty($config['source_url'])){ $this->error('请先完善起始页网址！'); } if(empty($config['field_list'])){ $this->error('请先完善字段列表！'); } $this->assign('collData',$collData); $this->display(); } }