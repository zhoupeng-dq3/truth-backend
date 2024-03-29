<?php
namespace app\api\exception;

use think\Exception;
use think\exception\Handle;
use think\exception\HttpException;
use think\Request;
use think\Db;

/**
* 
*/
class BaseException extends Exception
{
	
	public $code='404';
	public $msg='参数错误';
	public $errorcode='100001';

	public function __construct(array $params)
	{
		if (array_key_exists('code', $params)) {$this->code=$params['code'];}
		if (array_key_exists('msg', $params)) {$this->msg=$params['msg'];}
		if (array_key_exists('errorcode', $params)) {$this->errorcode=$params['errorcode'];}
	}
}
