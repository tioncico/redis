<?php
/**
 * Created by PhpStorm.
 * User: Tioncico
 * Date: 2019/9/24 0024
 * Time: 16:16
 */

namespace Test;

use EasySwoole\Redis\Client;
use EasySwoole\Redis\Config\RedisConfig;
use EasySwoole\Redis\Redis;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine;

class RedisTest extends TestCase
{
    /**
     * @var $redis Redis
     */
    protected $redis;

    protected function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $redis = new Redis(new RedisConfig());
        $redis->connect();
        $redis->auth('easyswoole');
        $this->redis = $redis;
    }

    function testConnect()
    {
        $redis = $this->redis;
        $data = $redis->connect();
        $this->assertTrue($data);
        $data = $redis->auth('easyswoole');
//        $this->assertTrue($data);
        $data = $redis->echo('test');
        $this->assertEquals('test', $data);
        $data = $redis->ping();
        $this->assertEquals('PONG', $data);
        $data = $redis->select(0);
        $this->assertTrue($data);
    }

    /**
     * key值操作测试
     * testKey
     * @author Tioncico
     * Time: 10:02
     */
    function testKey()
    {
        $redis = $this->redis;
        $key = 'test123213Key';
        $redis->select(0);
        $redis->set($key, 123);
        $data = $redis->dump($key);
        $this->assertTrue(!!$data);

        $data = $redis->dump($key . 'x');
        $this->assertNull($data);

        $data = $this->redis->exists($key);
        $this->assertEquals(1, $data);

        $data = $this->redis->expire($key, 1);
        $this->assertEquals(1, $data);
        Coroutine::sleep(2);
        $this->assertEquals(0, $this->redis->exists($key));

        $redis->expireAt($key, 1 * 100);
        Coroutine::sleep(0.1);
        $this->assertEquals(0, $this->redis->exists($key));

        $redis->set($key, 123);
        $data = $redis->keys("{$key}*");
        $this->assertEquals($key, $data[0]);

        $redis->select(1);
        $redis->del($key);
        $redis->select(0);
        $data = $redis->move($key, 1);
        $this->assertEquals(1, $data);
        $data = $redis->exists($key);
        $this->assertEquals(0, $data);
        $redis->select(0);

        $redis->set($key, 123);
        $data = $redis->expire($key, 1);
        $this->assertEquals(1, $data);
        $data = $redis->persist($key);
        $this->assertEquals(1, $data);

        $redis->expire($key, 1);
        $data = $redis->pTTL($key);
        $this->assertLessThanOrEqual($data, 1000);

        $data = $redis->ttl($key);
        $this->assertLessThanOrEqual($data, 1);

        $data = $redis->randomKey();
        $this->assertTrue(!!$data);
        $data = $redis->rename($key, $key . 'new');
        $this->assertTrue($data);
        $this->assertEquals(1, $redis->expire($key . 'new'));
        $this->assertEquals(0, $redis->expire($key));

        $data = $redis->renameNx($key, $key . 'new');
        $this->assertEquals(0, $data);
        $redis->renameNx($key . 'new', $key);
        $data = $redis->renameNx($key, $key . 'new');
        $this->assertEquals(1, $data);
        $data = $redis->type($key);
        $this->assertEquals('none',$data);
        $data = $redis->type($key. 'new');
        $this->assertEquals('string',$data);
    }

    /**
     * 字符串单元测试
     * testString
     * @author tioncico
     * Time: 下午9:41
     */
    function testString()
    {
        $redis = $this->redis;
        $key = 'test';
        $value = 1;
        $data = $redis->del($key);
        $this->assertNotFalse($data);
        $data = $redis->set($key, $value);
        $this->assertTrue($data);

        $data = $redis->get($key);
        $this->assertEquals($data, $value);

        $data = $redis->exists($key);
        $this->assertEquals(1, $data);

        $data = $redis->set($key, $value);
        $this->assertTrue($data);
        $value += 1;
        $data = $redis->incr($key);
        $this->assertEquals($value, $data);

        $value += 10;
        $data = $redis->incrBy($key, 10);
        $this->assertEquals($value, $data);

        $value -= 1;
        $data = $redis->decr($key);
        $this->assertEquals($value, $data);

        $value -= 10;
        $data = $redis->decrBy($key, 10);
        $this->assertEquals($value, $data);

        $key='stringTest';
        $value='tioncico';
        $redis->set($key,$value);

        $data = $redis->getRange($key,1,2);
        $this->assertEquals('io',$data);

        $data = $redis->getSet($key,$value.'a');
        $this->assertEquals($data,$value);
        $redis->set($key,$value);

        $bitKey ='testBit';
        $bitValue=10000;
        $redis->set($bitKey,$bitValue);
        $data = $redis->setBit($bitKey,1,0);
        $this->assertEquals(0,$data);
        $data = $redis->getBit($key,1);
        $this->assertEquals(1,$data);


        $field = [
            'stringField1',
            'stringField2',
            'stringField3',
            'stringField4',
            'stringField5',
        ];
        $value = [
            1,
            2,
            3,
            4,
            5,
        ];
        $data = $redis->mSet([
            "{$field[0]}" => $value[0],
            "{$field[1]}" => $value[1],
            "{$field[2]}" => $value[2],
            "{$field[3]}" => $value[3],
            "{$field[4]}" => $value[4],
        ]);
        $this->assertTrue($data);
        $data = $redis->mGet($field[3],$field[2],$field[1]);
        $this->assertEquals([$value[3],$value[2],$value[1]],$data);


        $data = $redis->setEx($key,1,$value[0].$value[0]);
        $this->assertTrue($data);
        $this->assertEquals($value[0].$value[0],$redis->get($key));

        $data = $redis->pSetEx($key,1,$value[0]);
        $this->assertTrue($data);
        $this->assertEquals($value[0],$redis->get($key));



        $redis->del($key);
        $data = $redis->setNx($key,1);
        $this->assertEquals(1,$data);


        $redis->del($field[0]);
        $data = $redis->mSetNx([
            "{$field[0]}" => $value[0],
            "{$field[1]}" => $value[1],
        ]);
        $this->assertEquals(0,$data);
        $this->assertEquals($value[1],$redis->get($field[1]));
        $redis->del($field[1]);
        $data = $redis->mSetNx([
            "{$field[0]}" => $value[0]+1,
            "{$field[1]}" => $value[1]+1,
        ]);
        $this->assertEquals(1,$data);
        $this->assertEquals($value[0]+1,$redis->get($field[0]));


        $data = $redis->setRange($field[0],1,1);
        $this->assertEquals(2,$data);
        $this->assertEquals('2'.$value[0],$redis->get($field[0]));

        $data = $redis->strLen($field[0]);
        $this->assertEquals(2,$data);

        $redis->set($key,1);
        $data = $redis->incrByFloat($key,0.1);
        $this->assertEquals(1.1,$data);
        $data = $redis->appEnd($field[0],'1');
        $this->assertEquals($redis->strLen($field[0]),$data);
        $this->assertEquals('2'.$value[0].'1',$redis->get($field[0]));
    }

    /**
     * testHash
     * @author Tioncico
     * Time: 11:54
     */
    function testHash()
    {
        $key = 'hKey';
        $field = [
            'hField1',
            'hField2',
            'hField3',
            'hField4',
            'hField5',
        ];
        $value = [
            1,
            2,
            3,
            4,
            5,
        ];

        $redis = $this->redis;

        $data = $redis->hSet($key, $field[0], $value[0]);
        $this->assertNotFalse($data);

        $data = $redis->hGet($key, $field[0]);
        $this->assertEquals($data, $value[0]);

        $data = $redis->hExists($key, $field[0]);
        $this->assertEquals(1, $data);

        $data = $redis->hDel($key, $field[0]);
        $this->assertEquals(1, $data, $redis->getErrorMsg());

        $data = $redis->hExists($key, $field[0]);
        $this->assertEquals(0, $data);

        $data = $redis->hMSet($key, [
            "{$field[0]}" => $value[0],
            "{$field[1]}" => $value[1],
            "{$field[2]}" => $value[2],
            "{$field[3]}" => $value[3],
            "{$field[4]}" => $value[4],
        ]);
        $this->assertTrue($data);
        $data = $redis->hValS($key);
        sort($data);
        $this->assertEquals($value, $data);

        $data = $redis->hGetAll($key);
        $keyTmp = array_keys($data);
        sort($keyTmp);
        $this->assertEquals($field, $keyTmp);
        $valueTmp = array_values($data);
        sort($valueTmp);
        $this->assertEquals($value, $valueTmp);
        $this->assertEquals($value, [
            $data[$field[0]],
            $data[$field[1]],
            $data[$field[2]],
            $data[$field[3]],
            $data[$field[4]],
        ]);

        $data = $redis->hKeys($key);
        sort($data);
        $this->assertEquals($field, $data);

        $data = $redis->hLen($key);
        $this->assertEquals(count($field), $data);

        $data = $redis->hMGet($key, $field[0], $field[1], $field[2]);

        $this->assertEquals([1, 2, 3], $data);

    }
}
