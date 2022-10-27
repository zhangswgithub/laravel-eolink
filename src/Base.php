<?php
/**
 * User: ZhangShiWei
 * Date: 2022/10/24 17:58
 * IDE : PhpStorm
 */

namespace Redam\Eolink;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

abstract class Base
{
    protected const REQUEST_TYPE_RAW       = 1;
    protected const REQUEST_TYPE_JSON      = 2;
    protected const REQUEST_TYPE_FROM_DATA = 0;
    protected const REQUEST_TYPE_XML       = 3;
    protected const REQUEST_TYPE_BINARY    = 4;


    // 返回数据类型
    protected const RESPONSE_RAW  = 2;
    protected const RESPONSE_JSON = 0;

    // 请求方式
    protected const REQUEST_METHOD_TYPE_GET  = 1;
    protected const REQUEST_METHOD_TYPE_POST = 0;

    // 请求协议类型
    protected const REQUEST_PROTOCOL_HTTP   = 0;
    protected const REQUEST_PROTOCOL_HTTPS  = 1;


    // 接口状态  0已发布, 8设计中, 3待确定, 4开发, 6对接, 5测试, 9完成, 7异常, 1维护, 2废弃
    protected const STATUS_PUBLISH         = 0;
    protected const STATUS_MAINTAIN        = 1;
    protected const STATUS_DISCARD         = 2;
    protected const STATUS_TO_BE_DETERMINE = 3;
    protected const STATUS_DEVELOPMENT     = 4;
    protected const STATUS_TEST            = 5;
    protected const STATUS_DOCKING         = 6;
    protected const STATUS_ABNORMAL        = 7;
    protected const STATUS_DESIGN          = 8;
    protected const STATUS_FINISH          = 9;

    // 类型
    protected const TYPE_REQUEST  = 1;  // 请求
    protected const TYPE_RESPONSE = 2;  // 响应

    // 数据类型
    protected const TYPE_STR = 0;
    protected const TYPE_INT = 3;
    protected const TYPE_FLOAT = 4;
    protected const TYPE_DICT = 13;
    protected const TYPE_LIST = 12;


    protected $options;
    protected $connection = '';

    public function __construct(array $options = [])
    {
        $this->options = $this->getOption($options);
        $this->checkAccount();
        $this->connection = DB::connection($this->options['eolink_connection']);
    }

    protected function getOption(array $options = []): array
    {
        if(empty($options)){
            return app()['config']->get('eolink');
        }

        return array_merge( app()['config']->get('eolink'), $options);
    }

    /**
     * 参数以及响应已经格式化的数据
     * @param $api_doc_user_name
     * @param $api_name
     * @param $request_url
     * @param $request_method
     * @param $group_name
     * @param $project_name
     * @param $request_param_type
     * @param $request_data
     * @param $response_param_type
     * @param $response_data
     * @param $space_mame
     * @return void
     */
    abstract  function addApiInterface($api_doc_user_name,
                                       $api_name,
                                       $request_url,
                                       $request_method,
                                       $group_name = "",
                                       $project_name = "",
                                       $request_data = null,
                                       $response_data = null,
                                       $request_param_type = self::REQUEST_TYPE_JSON,
                                       $response_param_type = self::RESPONSE_JSON,
                                       $space_mame = "shop");

    abstract  function addGroup($group_name, $project_name, $parent_group_name = null, $space_mame = 'shop');

    abstract  function addProject($user_name, $project_name, $space_mame = 'shop', $project_desc = null);

    /**
     * 参数以及响应没有格式化的数据
     * @param $api_doc_user_name
     * @param $api_name
     * @param $request_url
     * @param $request_method
     * @param $group_name
     * @param $project_name
     * @param $request_param_type
     * @param $request_data
     * @param $response_param_type
     * @param $response_data
     * @param $space_mame
     * @return void
     */
    protected function addNotFormatApiInterface($api_doc_user_name,
                                             $api_name,
                                             $request_url,
                                             $request_method,
                                             $group_name = "",
                                             $project_name = "",
                                             $request_data = null,
                                             $response_data = null,
                                             $request_param_type = self::REQUEST_TYPE_JSON,
                                             $response_param_type = self::RESPONSE_JSON,
                                             $space_mame = "shop")
    {
        $request_data = $request_data ? $this->setFormatData($request_data, self::TYPE_REQUEST) : $request_data;
        $response_data = $response_data ? $this->setFormatData($response_data, self::TYPE_RESPONSE) : $response_data;
        return $this->addApiInterface($api_doc_user_name,
            $api_name,
            $request_url,
            $request_method,
            $group_name,
            $project_name,
            $request_data,
            $response_data,
            $request_param_type,
            $response_param_type,
            $space_mame);
    }

    /**
     * 请求参数和响应格式化
     * @param $param_data
     * @param $type
     * @return array
     */
    protected function setFormatData($param_data, $type)
    {
        $data = [];
        foreach ($param_data as $key => $val){
            $result = [];
            if(!is_array($val)) {
                $result['key'] = $key;
                $result['must'] = "是";
                $result['desc'] = "";
                $result['value'] = $val;
            }
            if(is_array($val)){
                if($type == self::TYPE_RESPONSE && isset($val[0]) && isset($val[1]) && is_array($val[0]) && is_array($val[1]) ){
                    if(count($val[0]) == count($val[1])){
                        $val = $val[0];
                    }else{
                        $val = count($val[0]) >= count($val[1]) ?  $param_data[$key][0] : $param_data[$key][1];
                    }
                }
                $result['key'] = $key;
                $result['desc'] = "";
                if(empty($val)){
                    $result['value'] = [];
                }else{
                    $result['value'] = "None";
                    $result['childList'] = $this->setFormatData($val, $type);
                }
            }
            $data[] = $result;
        }
        return $data;
    }

    /**
     * 获取表名
     * @param $table
     * @return string
     */
    protected function getTable($table)
    {
        if(! Schema::connection($this->options['eolink_connection'])->hasTable($table)){
            throw new \Exception($table  . ' 表不存在');
        }
        return $table;
    }

    /**
     * 校验数据库是否可以正常连接
     * @return \PDO
     * @throws \Exception
     */
    protected function checkAccount()
    {
        try {
            return new \PDO("mysql:host=" . $this->options['eolink_host'] . ";dbname=" . $this->options['eolink_database'], $this->options['eolink_username'], $this->options['eolink_password']);
        }catch (\Exception $exception){
            throw new \Exception($this->options['eolink_host'] . ':' . $this->options['eolink_port'] . " 数据库积极拒绝，请检查配置");
        }
    }
}