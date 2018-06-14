<?php

namespace Noking50\User;

use Session;
use Cookie;
use Cache;
use Log;

/**
 * User
 * 
 */
class User {

    /**
     * 使用者群組 backend admin、 member、 etc...
     *
     * @var stirng 
     */
    private $userGroup = null;

    /**
     * Construct
     * 
     * @param string $group 使用者群組
     */
    public function __construct($group = 'member') {
        $this->group($group);
    }

    /**
     * 存取使用者群組 
     * 
     * @param string|null $group null為取得目前的群組
     * @return $this|string
     * @throws \Exception
     */
    public function group($group = null) {
        if (is_null($group)) {
            return $this->userGroup;
        } else {
            if (is_null(config('user.group.' . $group))) {
                $errmsg = 'User group "' . $group . '" not found.';
                Log::error($errmsg);
                throw new \Exception($errmsg);
            }
            $this->userGroup = $group;
            return $this;
        }
    }

    /**
     * 是否為超級管理員帳號
     * 
     * @param string $account 帳號
     * @return boolean
     */
    public function isSuperAccount($account) {
        return $account === config('user.group.' . $this->userGroup . '.super.account');
    }

    /**
     * 是否為超級管理員密碼
     * 
     * @param string $password 已雜湊的密碼
     * @return boolean
     */
    public function isSuperPassword($password) {
        return $password === config('user.group.' . $this->userGroup . '.super.password');
    }

    /**
     * 雜湊密碼
     * 
     * @param string $password 明文密碼
     * @return string
     */
    public function hashPassword($password, $salt = '') {
        return hash('sha256', config('user.pwd_enc_pre') . $password . config('user.pwd_enc_post') . $salt);
    }

    // Session

    /**
     * 使用者登入
     * 
     * @param array $data 登入資訊
     * @return void
     */
    public function login($data) {
        if (isset($data['id'])) {
            Session::put($this->userGroup . '.basic', $data);
        }
    }

    /**
     * 使用者登出
     * 
     * @return void
     */
    public function logout() {
        Session::forget($this->userGroup . '.basic');
    }

    /**
     * 使用者是否已登入
     * 
     * @return boolean
     */
    public function isLogin() {
        return !is_null(Session::get($this->userGroup . '.basic.id'));
    }

    /**
     * 使用者是否有權限進入
     * 
     * @param string $key 權限 網址的dot路徑
     * @return boolean
     */
    public function isAccess($key) {
        return in_array($key, Session::get($this->userGroup . '.basic.permission', []));
    }

    /**
     * 取得使用者ID 
     * 
     * @param string $key 若為null取得id 或複合主鍵的陣列，若有設定值 取得複合主鍵取得此欄位的值
     * @return mix
     */
    public function id($key = null) {
        if (is_null($key)) {
            return Session::get($this->userGroup . '.basic.id');
        } else {
            return Session::get($this->userGroup . '.basic.id.' . $key);
        }
    }

    /**
     * 取得使用者屬性 
     * 
     * @param string $key 屬性路徑
     * @param mixed $default 預設值
     * @return mix
     */
    public function get($key = '', $default = null) {
        return Session::get($this->userGroup . rtrim('.' . $key, '.'), $default);
    }

    /**
     * 設定使用者屬性 
     * 
     * @param string|array $key 屬性路徑
     * @param mixed $value 設定值
     * @return void
     */
    public function set($key, $value) {
        return Session::put($this->userGroup . '.' . $key, $value);
    }

    /**
     * 使用者屬性是否有設定值 
     * 
     * @param string|array $key 屬性路徑
     * @return boolean
     */
    public function has($key) {
        return Session::has($this->userGroup . '.' . $key);
    }

    /**
     * 刪除使用者屬性
     * 
     * @param string|array $key 屬性路徑
     * @return void
     */
    public function forget($key) {
        return Session::forget($this->userGroup . '.' . $key);
    }

    /**
     * 使用者點擊資料
     * 
     * @param string $group 群組資料(table)
     * @param string $id 資料主鍵
     * @return boolean 成功點擊回傳true, 已點擊或點擊失敗回傳false
     */
    public function click($group, $id) {
        $arr_click = $this->getClick($group);
        $result = false;
        if (!is_null($id) && !in_array($id, $arr_click)) {
            array_unshift($arr_click, $id);
            $result = true;
        }
        $this->setClick($group, $arr_click);

        return $result;
    }

    /**
     * 使用者取消點擊資料
     * 
     * @param string $group 群組資料(table)
     * @param string $id 資料主鍵
     * @return void
     */
    public function unclick($group, $id) {
        $arr_click = $this->getClick($group);
        if (!is_null($id) && in_array($id, $arr_click)) {
            $search_keys = array_keys($arr_click, $id);
            foreach ($search_keys as $k => $v) {
                unset($arr_click[$v]);
            }
            $this->setClick($group, $arr_click);
        }
    }

    /**
     * 使用者是否已點擊 群組資料(table)的某筆資料
     * 
     * @param string $group 群組資料(table)
     * @param string $id 資料主鍵
     * @return boolean
     */
    public function isClick($group, $id) {
        $arr_click = $this->getClick($group);
        return (!is_null($id) && in_array($id, $arr_click));
    }

    /**
     * 取得使用者在 群組資料(table) 底下的點擊紀錄
     * 
     * @param string $group 群組資料(table)
     * @return array
     */
    public function getClick($group) {
        $key = 'click_' . $group;
        $arr_cook = unserialize(\Cookie::get($this->userGroup . '_' . $key, serialize(array())));
        $arr_sess = $this->get($key, array());
        $arr_merge = array_unique(array_merge(is_array($arr_cook) ? $arr_cook : [], is_array($arr_sess) ? $arr_sess : []));

        return $arr_merge;
    }

    /**
     * 設定使用者在 群組資料(table) 底下的點擊紀錄
     * 
     * @param string $group 群組資料(table)
     * @param array $arr_click 點擊紀錄
     * @return void
     */
    public function setClick($group, $arr_click) {
        $key = 'click_' . $group;
        Cookie::queue(Cookie::forever($this->userGroup . '_' . $key, serialize($arr_click)));
        $this->set($key, $arr_click);
    }

    // Cache    

    /**
     * 取得使用者紀錄cache資料的key的 cache key name
     * 
     * @return string
     */
    protected function cacheAllKeysName() {
        $id = $this->id();
        if(is_array($id)){
            $id = implode('_', $id);
        }
        return 'user_allkeys_' . $this->userGroup . '_' . $id;
    }

    /**
     * 取得cache資料的key的 prefix
     * 
     * @return string
     */
    protected function cacheKeyName() {
        $id = $this->id();
        if(is_array($id)){
            $id = implode('_', $id);
        }
        return 'user_' . $this->userGroup . '_' . $id;
    }

    /**
     * 清空使用者產生的cache
     * 
     * @return void
     */
    public function cacheClear() {
        $cache_all_keys_name = $this->cacheAllKeysName();
        $user_all_keys = Cache::get($cache_all_keys_name, []);
        foreach ($user_all_keys as $k => $v) {
            Cache::forget($v);
        }
        Cache::forget($cache_all_keys_name);
    }

    /**
     * 取得cache資料
     * 
     * @param string $key cache資料key
     * @param mixed $default 預設值
     * @return mixed
     */
    public function cacheGet($key, $default = null) {
        return Cache::get($this->cacheKeyName() . '_' . $key, $default);
    }

    /**
     * 設定cache資料
     * 
     * @param string $key cache資料key
     * @param mixed $value cache資料值
     */
    public function cacheSet($key, $value) {
        $cache_all_keys_name = $this->cacheAllKeysName();
        $user_all_keys = Cache::get($cache_all_keys_name, []);
        $cache_key_name = $this->cacheKeyName() . '_' . $key;

        Cache::forever($cache_key_name, $value);
        if (!in_array($cache_key_name, $user_all_keys)) {
            $user_all_keys[] = $cache_key_name;
            Cache::forever($cache_all_keys_name, $user_all_keys);
        }
    }

    /**
     * 刪除cache資料
     * 
     * @param string $key cache資料key
     * @return void
     */
    public function cacheForget($key) {
        $cache_all_keys_name = $this->cacheAllKeysName();
        $user_all_keys = Cache::get($cache_all_keys_name, []);
        $cache_key_name = $this->cacheKeyName() . '_' . $key;

        Cache::forget($cache_key_name);
        if (($tmpKey = array_search($cache_key_name, $user_all_keys)) !== false) {
            unset($user_all_keys[$tmpKey]);
            Cache::forever($cache_all_keys_name, $user_all_keys);
        }
    }

}
