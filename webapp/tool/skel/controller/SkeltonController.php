<?php
/**
 * <{$TableName}><{if $isAdmin}>管理<{/if}>コントローラ
 */
<{if $isAdmin}>
class <{$ModuleName}>_<{$TableName}>AdminController extends Ao_Controller_ModAdmAction
<{else}>
class <{$ModuleName}>_<{$TableName}>Controller extends Ao_Controller_ModAclAction
<{/if}>
{
    /**
     * 一覧表示
     */
    public function indexAction()
    {
        // 全件表示の場合はこちら
        //$manager = new <{$ModuleName}>_Manager_<{$TableName}>($this);
        //$this->view->items = $manager->get<{$TableName}>();

        // ページャを使う
        $req = $this->getRequest();
        $m = $req->getModuleName();
        $c = $req->getControllerName();
        $kw = $req->getParam("kw");
        $manager = new <{$ModuleName}>_Manager_<{$TableName}>($this);
        $count = $manager->getCount($kw);
        $this->view->count = $count;
        $reg = Zend_Registry::getInstance();
        $conf = $reg["config"];
        $modconf = $reg["modconf"]->$m;
        $offset = (int)$req->getParam("offset");
        $limit = $req->getParam("limit");
        if($limit == ""){
            $limit = $modconf->search->limit;
        }
        $base_url = $conf->site->root_url."/$m/$c/?limit=$limit&amp;offset=";
        $nav = new Ao_Util_Pagenav();
        $nav->set("total", $count);
        $nav->set("perpage", $limit);
        $nav->set("current", $offset);
        $nav->set("url", $base_url);
        $this->view->pagenav = $nav->renderNav();
        $this->view->items = $manager->getPage($limit, $offset, null, $kw);
        $this->view->kw = $kw;
    }
    /**
     * 詳細表示
     */
    public function detailAction()
    {   
        $id = $this->getRequest()->getQuery("id");
        if(!$id){ 
            $msg = "ID が取得できませんでした";
            throw new Zend_Exception($msg);
        }
        $manager = new <{$ModuleName}>_Manager_<{$TableName}>($this);
        $vo = $manager->get<{$TableName}>($id);
        if(!$vo){ 
            $msg = "データが取得できませんでした";
            throw new Zend_Exception($msg);
        }
        $this->view->item = current($vo);
    }
    /**
     * 新規登録
     */
    public function newAction()
    {
        $req = $this->getRequest();
        $form = new <{$ModuleName}>_Form_<{$TableName}>($this);
        $this->view->form = $form->formElements();
        $this->view->action_name 
            = $this->_registry["config"]->site->root_url
                ."/".$req->getModuleName()
                ."/".$req->getControllerName()
                ."/".$req->getActionName()
                ."/";
        if($req->isPost()){
            $manager = new <{$ModuleName}>_Manager_<{$TableName}>($this);
            $params = $req->getParams();
            //$manager->convYmdToIntPubDate($params);
            //$manager->convYmdToIntExprDate($params);

            // _forward 用変数
            $m = $req->getModuleName();
            $c = $req->getControllerName();
        
            // バリデーション
            $form = new <{$ModuleName}>_Form_<{$TableName}>($this);
            $form->validate($params);
            if(!$form->errorNum()){ 
                $vo = $manager->get<{$TableName}>Vo($params);
                $manager->save<{$TableName}>($vo);
                $this->view->message = "登録が完了しました";
                $this->getFrontController()->setParam("noViewRenderer", true);
                $c = $req->getControllerName();
                $a = $req->getActionName();
                echo $this->view->render($c."/".$a."_complete.html");
                exit;
            }
            $this->view->error = $form->errors();
        }
    }
    /**
     * 編集フォーム画面表示
     */
    public function editAction()
    {
        $req = $this->getRequest();
        $id = $req->getParam("id");
        if(!$id){
            throw new Zend_Exception("IDが取得できませんでした");
        }
        // DB データを取得する
        $manager = new <{$ModuleName}>_Manager_<{$TableName}>($this);
        $vos = $manager->get<{$TableName}>($id);
        $vo = current($vos);

        // 情報公開日を年月日時分に分割してセットする
        //$manager->convIntToYmdPubDate($vo);

        // 公開終了日を年月日時分に分割してセットする
        //$manager->convIntToYmdExprDate($vo);

        // _REQUEST データを DBデータをマージする
        $_params = $req->getParams();
        $params = array_merge($vo->toArray(), $_params);

        $reg = Zend_Registry::getInstance();
        $conf = $reg["config"];

        // フォーム要素を準備する
        $form = new <{$ModuleName}>_Form_<{$TableName}>($this);
        $form->setParams($params);
        $action_name = $conf->site->root_url
            ."/".$req->getModuleName()
            ."/".$req->getControllerName()
            ."/edit-confirm/";

        // ビューにアサインする
        $this->view->form = $form->formElements();
        $this->view->action_name = $action_name;
        $this->view->params = $params;
    }
    /**
     * 編集内容確認画面表示
     */
    public function editConfirmAction()
    {
        $req = $this->getRequest();
        $id = $req->getParam("id");
        if(!$id){
            throw new Zend_Exception("記事のIDが取得できませんでした");
        }
        // DB データを取得する
        $manager = new <{$ModuleName}>_Manager_<{$TableName}>($this);
        $vos = $manager->get<{$TableName}>($id);
        $vo = current($vos);

        // 送信データを取得
        $_params = $req->getParams();

        // _forward 用変数
        $m = $req->getModuleName();
        $c = $req->getControllerName();

        // バリデーション
        $form = new <{$ModuleName}>_Form_<{$TableName}>($this);
        $form->validate($_params);
        if($form->errorNum()){
            $this->view->error = $form->errors();
            return $this->_forward("edit", $c, $m);
        }

        // DB データと送信データをマージする
        $params = array_merge($vo->toArray(), $_params);

        $reg = Zend_Registry::getInstance();
        $conf = $reg["config"];

        // POST で確認ボタンもしくは登録ボタンなら処理をする
        if(
            $req->isPost()
            && ($req->getPost("submit") == "確認"
                || $req->getPost("submit") == "登録")
        ){
            // フォーム要素を準備する
            $form->setParams($params);
            $action_name = $conf->site->root_url
                ."/".$req->getModuleName()
                ."/".$req->getControllerName()
                ."/edit-regist/";

            // 送信データを hidden タグにうめてアサイン
            $this->view->form = $form->confirmElements();
            $this->view->action_name = $action_name;
            $this->view->params = $params;
        }
        else {
            $req->setParam("sys_message", "不正なアクションを検出しました");
            return $this->_forward("edit", $c, $m);
        }
    }
    /**
     * 編集内容保存
     */
    public function editRegistAction()
    {
        $req = $this->getRequest();
        $id = $req->getParam("id");
        if(!$id){
            throw new Zend_Exception("ID が取得できませんでした");
        }
        // DB データを取得
        $manager = new <{$ModuleName}>_Manager_<{$TableName}>($this);
        $vos = $manager->get<{$TableName}>($id);
        $vo = current($vos);
        
        // 送信データを取得
        $_params = $req->getParams();
        
        // _forward 用変数
        $m = $req->getModuleName();
        $c = $req->getControllerName();
            
        // バリデーション
        $form = new <{$ModuleName}>_Form_<{$TableName}>($this);
        $form->validate($_params);
        if($form->errorNum()){
            // エラーがあったらメッセージをアサインしてフォームに戻す
            $this->view->error = $form->errors();
            return $this->_forward("edit", $c, $m);
        }
        
        // DB データと送信データをマージする
        $params = array_merge($vo->toArray(), $_params);
                
        // ボタンに応じたアクション
        if($req->isPost() && $req->getPost("submit") == "戻る"){
            return $this->_forward("edit", $c, $m);
        }   
        else if(
            $req->isPost() && $req->getPost("submit") == "登録"
        ){      
            // フォーム要素を準備する
            $form->setParams($params);
            $conf = $this->_registry["config"];
            $action_name = $conf->site->root_url
                ."/".$req->getModuleName()
                ."/".$req->getControllerName()
                ."/";
        
            //$manager->convYmdToIntPubDate($params);
            //$manager->convYmdToIntExprDate($params);
            $vo = $manager->get<{$TableName}>Vo($params);
            $manager->save<{$TableName}>($vo);
     
            $this->view->action_name = $action_name;
            $this->view->params = $params;
        }
    }
    /**
     * 削除確認画面
     */
    public function deleteAction()
    {
        $req = $this->getRequest();
        $id = $req->getParam("id");
        if(!$id){
            throw new Zend_Exception("IDが取得できませんでした");
        }
        // DB データを取得する
        $manager = new <{$ModuleName}>_Manager_<{$TableName}>($this);
        $vos = $manager->get<{$TableName}>($id);
        $vo = current($vos);

        $reg = Zend_Registry::getInstance();
        $action_name = $reg["config"]->site->root_url
                ."/".$req->getModuleName()
                ."/".$req->getControllerName()
                ."/delete-do/";

        // ビューにアサインする
        $this->view->action_name = $action_name;
        $this->view->item = $vo;
    }
    /**
     * 削除処理
     */
    public function deleteDoAction()
    {
        $req = $this->getRequest();
        $id = $req->getParam("id");
        if(!$id){
            throw new Zend_Exception("IDが取得できませんでした");
        }

        // DB データを取得する
        $manager = new <{$ModuleName}>_Manager_<{$TableName}>($this);
        $_item = $manager->get<{$TableName}>($id);
        if(!$_item){
            throw new Zend_Exception("データが取得できませんでした");
        }
        $item = current($_item);

        // 削除
        $manager->delete<{$TableName}>($item->get("id"));

        $this->view->item = $item;
    }

    /**
     * tabledefAction()
     *
     * for debug Action(公開時は削除すること)
     */
    public function tabledefAction()
    {
        $model = new <{$ModuleName}>_Model_<{$TableName}>();
        $info = $model->info();
        $html = null;
        $html.= "<h2>{$info["name"]}</h2>\n";
        $html.= "<table border='1'>\n";
        $html.="<tr><th>column name</th><th>data type</th><th>default</th></tr>\n";
        foreach($info["metadata"] as $row){            $html .= "<tr><td>{$row["COLUMN_NAME"]}</td><td>{$row["DATA_TYPE"]}</td><td>{$row["DEFAULT"]}</td></tr>\n";        }
        $html.= "</table>\n";
        $html.= "Primary key: ".implode(",", $info["primary"]);
        print $html;
        exit; 
    }
}
