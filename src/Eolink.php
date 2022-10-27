<?php

namespace Redam\Eolink;


class Eolink extends Base
{
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
    public function addNotFormatApiInterface($api_doc_user_name,
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
        $request_data = $request_data ? $this->setFormatData($request_data, self::TYPE_REQUEST) : null;
        $response_data = $response_data ? $this->setFormatData($response_data, self::TYPE_RESPONSE) : null;
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
     * 添加参数以及响应已经格式化的数据
     * @param $api_doc_user_name  文档创建者
     * @param $api_name  api接口名称
     * @param $request_url  接口地址
     * @param $request_method  请求方式 0.PSOT,  1.GET
     * @param $group_name  分组名称
     * @param $project_name  项目名称
     * @param $request_param_type  请求参数类型
     * @param $request_data 请求参数
     * @param $response_param_type  返回数据类型
     * @param $response_data  返回数据
     * @param $space_mame  空间名称
     * @return void
     */
    public function addApiInterface($api_doc_user_name,
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
        $user_table = $this->getTable('user');
        $user_data = $this->connection->table($user_table)->where([
            "userName" => $api_doc_user_name,
            "isRemove" => 0
        ])->first();

        if(empty($user_data)){
            throw new \Exception("未找到用户信息 请检查用户名是否正确");
        }
        $data = [
            "apiName" => $api_name,  # 接口名称
            "apiURI" => $request_url,  # 接口路径
            "apiProtocol" => self::REQUEST_PROTOCOL_HTTPS,  # 请求协议类型 ----#0http, 1https
            "apiFailureMock" => "",  # 失败示例
            "apiSuccessMock" => "",  # 成功示例
            "apiRequestType" => strtoupper($request_method) == 'POST' ? self::REQUEST_METHOD_TYPE_POST : self::REQUEST_METHOD_TYPE_GET,  # 请求方式----# 0.PSOT,  1.GET
            "apiStatus" => self::STATUS_PUBLISH,  # 接口状态----#0已发布, 8设计中, 3待确定, 4开发, 6对接, 5测试, 9完成, 7异常, 1维护, 2废弃
            "apiUpdateTime" => date('Y-m-d H:i:s'),  # 接口更新时间
            "groupID" => '',  # 分组ID
            "projectID" => '',  # 项目ID
            "apiNoteType" => 1,  # 详细说明类型
            "apiNoteRaw" => '',  # 详细说明markdown内容
            "apiNote" => '',  # 详细说明富文本内容
            "apiRequestParamType" => $request_param_type,  # 请求参数类型(0:FROM-DATA，1:RAW，2:JSON，3:XML，4:Binary)
            "apiRequestRaw" => $request_data,  # 请求参数源数据  raw格式
            "apiRequestBinary" => '',  # 请求参数二进制数据
            "updateUserID" => $user_data->userID,  # 更新者ID
            "mockRule" => '',  # mock规则
            "mockResult" => '',  # mock结果
            "mockConfig" => '',  # mock配置
            "createTime" => date('Y-m-d H:i:s'),  # 创建时间
            "apiFailureStatusCode" => 200,  # 失败返回状态码
            "apiSuccessStatusCode" => 200,  # 成功返回状态码
            "beforeInject" => '',  # 前注入
            "afterInject" => '',  # 后注入
            "createUserID" => $user_data->userID,  # 创建者
            "authInfo" => '{"status":"0"}',  # 鉴权
            "apiFailureContentType" => 'text/html; charset=UTF-8',  # 失败示例ContentType
            "apiSuccessContentType" => 'text/html; charset=UTF-8',  # 成功示例ContentType
            "apiManagerID" => $user_data->userID,  # API负责人
            "apiType" => 'https',  # 接口类型
            "customInfo" => '{"messageEncoding":"utf-8"}',  # 自定义信息字段
            "orderNum" => 0,  # 排序
            "group_name" => $group_name,
            "project_name" => $project_name,
            "userID" => $user_data->userID,
            "result" => $response_data,
            "resultParamType" => $response_param_type,  # 0:json 2:RAW
        ];

        $workspace_table = $this->getTable('workspace');
        $workspace_data = $this->connection->table($workspace_table)->where('spaceName', $space_mame)->first();

        if(!$workspace_data){
            throw new \Exception("空间未找到 请检查空间名称是否正确");
        }

        $ams_project_table = $this->getTable('ams_project');
        $ams_project_data = $this->connection->table($ams_project_table)->where([
            'spaceID' =>  $workspace_data->spaceID,
            'projectName' => $project_name
        ])->first();
        if(!$ams_project_data){
            throw new \Exception("项目未找到 请检查项目名称是否正确");
        }
        $data['projectID'] = $ams_project_data->projectID;

        $ams_api_group_table = $this->getTable('ams_api_group');
        $ams_api_group_data = $this->connection->table($ams_api_group_table)->where([
            'groupName' => $group_name,
            'projectID' => $ams_project_data->projectID
        ])->first();
        $groupID = $ams_api_group_data ? $ams_api_group_data->groupID : $this->addGroup($group_name, $project_name, '', $space_mame);
        $data['groupID'] = $groupID;

        try {
            $result = $this->insertOrUpdate($data);
        }catch (\Exception $exception){
            throw new \Exception($exception->getMessage());
        }
        return $result;
    }

    /**
     * @param $api_doc_user_name  文档创建者
     * @param $api_name  api接口名称
     * @param $request_url  接口地址
     * @param $request_method  请求方式 0.PSOT,  1.GET
     * @param $request_param_type  请求参数类型
     * @param $request_data 请求参数
     * @param $response_param_type  返回数据类型
     * @param $response_data  返回数据
     * @param $group_name  分组名称
     * @param $project_name  项目名称
     * @param $space_mame  空间名称
     * @return void
     */
    private function insertOrUpdate($data)
    {
        $ams_api_table = $this->getTable('ams_api');
        $ams_api_data = $this->connection->table($ams_api_table)->where([
            "apiURI" => $data['apiURI'],
            "projectID" => $data['projectID']
        ])->first();
        $insertOrUpdateData = [
            'apiName' => $data['apiName'],  # 接口名称
            'apiURI' => $data['apiURI'],  # 接口路径
            'apiProtocol' => $data['apiProtocol'],  # 请求协议类型
            'apiFailureMock' => $data['apiFailureMock'],  # 失败示例
            'apiSuccessMock' => $data['apiSuccessMock'],  # 成功示例
            'apiRequestType' => $data['apiRequestType'],  # 接口名称
            'apiStatus' => $data['apiStatus'],  # 接口状态
            'apiUpdateTime' => $data['apiUpdateTime'],  # 接口更新时间
            'groupID' => $data['groupID'],  # 分组ID
            'projectID' => $data['projectID'],  # 项目ID
            'apiNoteType' => $data['apiNoteType'],  # 详细说明类型
            'apiNoteRaw' => $data['apiNoteRaw'],  # 详细说明markdown内容
            'apiNote' => $data['apiNote'],  # 详细说明富文本内容
            'apiRequestParamType' => $data['apiRequestParamType'],
            # 请求参数类型( =>:$FROM-DATA，1:RAW，2:JSON，3:XML，4:Binary)
            'apiRequestRaw' => json_encode($data['apiRequestRaw']),  # 请求参数源数据
            'apiRequestBinary' => $data['apiRequestBinary'],  # 请求参数二进制数据
            'updateUserID' => $data['updateUserID'],  # 更新者ID
            'mockRule' => $data['mockRule'],  # mock规则
            'mockResult' => $data['mockResult'],  # mock结果
            'mockConfig' => $data['mockConfig'],  # mock配置
            'createTime' => $data['createTime'],  # 创建时间
            'apiFailureStatusCode' => $data['apiFailureStatusCode'],  # 失败返回状态码
            'apiSuccessStatusCode' => $data['apiSuccessStatusCode'],  # 成功返回状态码
            'beforeInject' => $data['beforeInject'],  # 前注入
            'afterInject' => $data['afterInject'],  # 后注入
            'createUserID' => $data['createUserID'],  # 创建者
            'authInfo' => $data['authInfo'],  # 鉴权
            'apiFailureContentType' => $data['apiFailureContentType'],  # 失败示例ContentType
            'apiSuccessContentType' => $data['apiSuccessContentType'],  # 成功示例ContentType
            'apiManagerID' => $data['apiManagerID'],  # API负责人
            'apiType' => $data['apiType'],  # 接口类型
            'customInfo' => $data['customInfo'],  # 自定义信息字段
            'orderNum' => $data['orderNum'],  # 排序
        ];

        if($ams_api_data){
            $result = $this->connection->table($ams_api_table)->where('apiID', $ams_api_data->apiID)->update($insertOrUpdateData);
        }else{
            $result = $this->connection->table($ams_api_table)->insert($insertOrUpdateData);
        }

        $ams_api_data = $this->connection->table($ams_api_table)->where([
            "apiURI" => $data['apiURI'],
            "projectID" => $data['projectID'],
        ])->first();
        $apiID = $ams_api_data->apiID;

        $ams_api_cache_table = $this->getTable('ams_api_cache');
        $ams_api_cache_data = $this->connection->table($ams_api_cache_table)->where('apiID', $apiID)->first();

        $cache_insertOrUpdateData = [
            'projectID' => $data['projectID'],
            'groupID' => $data['groupID'],
            'apiID' => $apiID,
            'apiJson' => $this->getCacheData($data),
            'starred' => 1,
            'updateUserID' => $data['userID'],
        ];

        if($ams_api_cache_data){
            $this->connection->table($ams_api_cache_table)->where('apiID', $apiID)->update($cache_insertOrUpdateData);
        }else{
            $this->connection->table($ams_api_cache_table)->insert($cache_insertOrUpdateData);
        }

        return $result;
    }

    /**
     * 设置内容格式
     * @param $data
     * @return string
     */
    private function getCacheData($data)
    {
        if($data['apiRequestParamType'] == self::REQUEST_TYPE_RAW){
            $apiRequestRaw = $data['apiRequestRaw'] ? json_encode($data['apiRequestRaw'], JSON_UNESCAPED_UNICODE) : null;
            $requestInfo = "null";
        }else{
            $apiRequestRaw = "null";
            $requestInfo = $data['apiRequestRaw'] ? json_encode($this->getResultData($data['apiRequestRaw'], self::TYPE_REQUEST), JSON_UNESCAPED_UNICODE) : null;
        }

        if($data['resultParamType'] == self::RESPONSE_RAW){
            $responseInfo = json_encode($data["result"], JSON_UNESCAPED_UNICODE);
        }else{
            $responseInfo = json_encode($this->getResultData($data["result"], self::TYPE_RESPONSE), JSON_UNESCAPED_UNICODE);
        }

        return '{' .
                '"baseInfo": {' .
                    '"apiName": "' . $data['apiName'] . '",' .
                    '"apiURI": "' . $data['apiURI'] . '",' .
                    '"apiProtocol": "' . $data['apiProtocol'] . '",' .
                    '"apiSuccessMock": "' . $data['apiSuccessMock'] . '",' .
                    '"apiFailureMock": "' . $data['apiFailureMock'] . '",' .
                    '"apiRequestType": "' . $data['apiRequestType'] . '",' .
                    '"apiStatus": "' . $data['apiStatus'] . '",' .
                    '"starred": 0,' .
                    '"apiNoteType": "' . $data['apiNoteType'] . '",' .
                    '"apiNoteRaw": "' . $data['apiNoteRaw'] . '",' .
                    '"apiNote": "' . $data['apiNote'] . '",' .
                    '"apiRequestParamType": "' . $data['apiRequestParamType'] . '",' .
                    '"apiRequestRaw": "' . $apiRequestRaw . '",' .
                    '"apiRequestBinary":null,' .
                    '"apiFailureStatusCode":"200",' .
                    '"apiSuccessStatusCode":"200",' .
                    '"apiFailureContentType":"text/html; charset=UTF-8",' .
                    '"apiSuccessContentType":"text/html; charset=UTF-8",' .
                    '"apiRequestParamJsonType": 0,' .
                    '"advancedSetting":null,' .
                    '"beforeInject": "' . $data['beforeInject'] . '",' .
                    '"afterInject": "' . $data['afterInject'] . '",' .
                    '"createTime": "' . $data['createTime'] . '",' .
                    '"apiUpdateTime": "' . $data['apiUpdateTime'] . '",' .
                    '"apiTag":""' .
                '},' .
            '"responseHeader": [],' .
            '"headerInfo": [],' .
            '"authInfo": {' .
                '"status": "0"' .
            '},' .
            '"requestInfo": ' . $requestInfo . ',' .
            '"urlParam": [],' .
            '"restfulParam": [],' .
            '"resultInfo": ' . $responseInfo . ',' .
            '"resultParamJsonType": 0,' .
            '"resultParamType": "' . $data['resultParamType'] . '",' .
            '"structureID": "[]",' .
            '"databaseFieldID": "[]",' .
            '"globalStructureID": "[]",' .
            '"fileID": "",' .
            '"requestParamSetting": [],' .
            '"resultParamSetting": [],' .
            '"customInfo": {' .
                '"messageEncoding": "utf-8"' .
            '},' .
            '"soapVersion": null,' .
            '"tagID": []' .
        '}';
    }

    /**
     * 响应数据格式化
     * @param $data
     * @param $type
     * @return array
     */
    private function getResultData($data, $type)
    {
        $result = [];
        foreach ($data as $key => $val){
            $paramNotNull = isset($val['must']) && $val['must'] == '是' ? "0" : "1";
            $res = [
                "paramNotNull" => $paramNotNull,
                "paramName" => $val["desc"],
                "paramKey" => $val["key"],
                "type" => "0",
                "paramValueList" => [],
            ];
            if(isset($val['childList'])){
                $paramType = $this->getType($val["childList"]);
                $res['childList'] = $this->getResultData($val["childList"], $type);
            }else{
                $paramType = $this->getType($val["value"]);
                $res['paramValue'] = $val['value'];
            }

            $res['paramType'] = $paramType;
            $result[] = $res;
        }
        return $result;
    }

    /**
     * 获取每个响应字段的类型
     * @param $value
     * @return int
     */
    private function getType($value)
    {
        switch ($value){
            case is_string($value):
                $type = self::TYPE_STR;
                break;
            case is_float($value):
                $type = self::TYPE_FLOAT;
                break;
            case is_int($value):
                $type = self::TYPE_INT;
                break;
            case is_array($value):
                $type = self::TYPE_LIST;
                break;
            default:
                $type = self::TYPE_STR;
        }
        return $type;
    }

    /**
     * 添加分组
     * @param $group_name  分组名称
     * @param $project_name 项目名称
     * @param $parent_group_name  上级分组名称
     * @param $space_mame 空间名称
     * @return void
     * @throws \Exception
     */
    public function addGroup($group_name, $project_name, $parent_group_name = null, $space_mame = 'shop')
    {
        $workspace_table = $this->getTable('workspace');
        $workspace_data = $this->connection->table($workspace_table)->where('spaceName', $space_mame)->first();
        if(!$workspace_data){
            throw new \Exception("空间未找到 请检查空间名称是否正确");
        }

        $ams_project_table = $this->getTable('ams_project');
        $ams_project_data = $this->connection->table($ams_project_table)->where([
            "spaceID" => $workspace_data->spaceID,
            "projectName" => $project_name
        ])->first();
        if(!$ams_project_data){
            throw new \Exception("项目未找到 请检查项目名称是否正确");
        }


        $ams_api_group_table = $this->getTable('ams_api_group');
        if($parent_group_name){
            $ams_api_group_data = $this->connection->table($ams_api_group_table)->where([
                "groupName" => $parent_group_name,
                "projectID" => $ams_project_data->projectID
            ])->first();
            $parentGroupID = $ams_api_group_data->groupID;
            $groupDepth = 1;
        }else{
            $parentGroupID = 0;
            $groupDepth = 1;
        }
        $insert_data = [
            "groupName" => $group_name,
            "projectID" => $ams_project_data->projectID,
            "parentGroupID" => $parentGroupID,
            "groupDepth" => $groupDepth,
        ];
        $groupId = $this->connection->table($ams_api_group_table)->insertGetId($insert_data);

        // 获取分组路径
        $group_path_data = $this->getGroupPath($groupId);
        $this->connection->table($ams_api_group_table)->where('groupID', $groupId)->update([
            'groupPath' => $group_path_data['groupPath'],
            'groupDepth' =>$group_path_data['groupDepth'],
        ]);

        return $groupId;
    }

    /**
     * 获取分组路径
     * @param $groupID
     * @param $groupPath
     * @param $groupDepth
     * @return array
     * @throws \Exception
     */
    private function getGroupPath($groupID, $groupPath = '', $groupDepth = 0)
    {
        $ams_api_group_table = $this->getTable('ams_api_group');
        $ams_api_group_data = $this->connection->table($ams_api_group_table)->where([
            "groupID" => $groupID
        ])->first();
        if($ams_api_group_data){
            $groupPathTmp = $ams_api_group_data->groupPath ?  $ams_api_group_data->groupID . ',' : $ams_api_group_data->groupID;
            $groupPath = $groupPathTmp + (int)$groupPath;
            $groupDepth = (int)$groupDepth + 1;
            if((int)$ams_api_group_data->parentGroupID == 0){
                return [ 'groupPath' => $groupPath, 'groupDepth' => $groupDepth];
            }else{
                return $this->getGroupPath($ams_api_group_data->parentGroupID, $groupPath, $groupDepth);
            }
        }
        return [ 'groupPath' => $groupPath, 'groupDepth' => $groupDepth];
    }


    /**
     * 添加项目
     * @param $user_name 用户名
     * @param $project_name 项目名称
     * @param $space_mame 空间名称
     * @param $project_desc 项目描述
     * @return void
     * @throws \Exception
     */
    public function addProject($user_name, $project_name, $space_mame = 'shop', $project_desc = null)
    {
        $user_table = $this->getTable('user');
        $user_data = $this->connection->table($user_table)->where([
            "userName" => $user_name,
            "isRemove" => 0
        ])->first();
        if(empty($user_data)){
            throw new \Exception("未找到用户信息 请检查用户名是否正确");
        }

        $workspace_table = $this->getTable('workspace');
        $workspace_data = $this->connection-> table($workspace_table)->where('spaceName', $space_mame)->first();
        if(!$workspace_data){
            throw new \Exception("空间未找到 请检查空间名称是否正确");
        }

        $ams_project_table = $this->getTable('ams_project');
        $insert_data = [
            "projectName" => $project_name,
            "projectType" => 0,
            "projectDesc" => $project_desc,
            'projectCreateTime' => date('Y-m-d H:i:s'),
            'projectUpdateTime' => date('Y-m-d H:i:s'),
            "spaceID" => $workspace_data->spaceID,
            "createUserID" => $user_data->userID,
            'hashKey' => md5(time()) . chr(rand(65,90)) .  uniqid() . chr(rand(65,90)),
        ];

        return  $this->connection->table($ams_project_table)->insert($insert_data);
    }
}