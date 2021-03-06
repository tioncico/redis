<?php
namespace EasySwoole\Redis\CommandHandle;

use EasySwoole\Redis\CommandConst;
use EasySwoole\Redis\Redis;
use EasySwoole\Redis\Response;

class DebugSegfault extends AbstractCommandHandle
{
	public $commandName = 'DebugSegfault';


	public function handelCommandData(...$data)
	{

		$command = [CommandConst::DEBUGSEGFAULT];
		$commandData = array_merge($command,$data);
		return $commandData;
	}


	public function handelRecv(Response $recv)
	{
		return $recv->getData();
	}
}
