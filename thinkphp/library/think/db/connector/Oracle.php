<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think\db\connector;

use PDO;
use think\db\Connection as BaseConnection;

/**
 * Oracle数据库驱动
 */
class Oracle extends BaseConnection
{

    protected $builder = '\\think\\db\\builder\\Oracle';

    /**
     * 解析pdo连接的dsn信息
     * @access protected
     * @param array $config 连接信息
     * @return string
     */
    protected function parseDsn($config)
    {
        $dsn = 'oci:dbname=';
        if (!empty($config['hostname'])) {
            //  Oracle Instant Client
            $dsn .= '//' . $config['hostname'] . ($config['hostport'] ? ':' . $config['hostport'] : '') . '/';
        }
        $dsn .= $config['database'];
        if (!empty($config['charset'])) {
            $dsn .= ';charset=' . $config['charset'];
        }
        return $dsn;
    }

    /**
     * 取得数据表的字段信息
     * @access public
     * @param string $tableName
     * @return array
     */
    public function getFields($tableName)
    {
        $this->initConnect(true);
        list($tableName) = explode(' ', $tableName);
        //editby aihuiwu@163.com  此处有修改。 原本的代码效率太低，导致取表字段信息太久。
        $sql = "select a.column_name,data_type,DECODE (nullable, 'Y', 0, 1) notnull, data_default,nvl( b.pk,0) pk  from user_tab_columns a,(select column_name,1 as pk from user_constraints c, user_cons_columns col where c.constraint_name = col.constraint_name and c.constraint_type = 'P' and c.table_name = '" . strtoupper($tableName) . "' ) b where a.table_name = '" . strtoupper($tableName) . "' and a.column_name = b.column_name(+)";
        $pdo = $this->linkID->query($sql);
        $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
        $info = [];

        if ($result) {
            foreach ($result as $key => $val) {
                $val = array_change_key_case($val);
                $info[$val['column_name']] = [
                    'name' => $val['column_name'],
                    'type' => $val['data_type'],
                    'notnull' => $val['notnull'],
                    'default' => $val['data_default'],
                    'primary' => $val['pk'],
                    'autoinc' => $val['pk'],
                ];
            }
        }
        return $this->fieldCase($info);
    }

    /**
     * 取得数据库的表信息（暂时实现取得用户表信息）
     * @access   public
     * @param string $dbName
     * @return array
     */
    public function getTables($dbName = '')
    {
        $pdo = $this->linkID->query("select table_name from user_tables");
        $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
        $info = [];
        foreach ($result as $key => $val) {
            $info[$key] = current($val);
        }
        return $info;
    }

    /**
     * SQL性能分析
     * @access protected
     * @param string $sql
     * @return array
     */
    protected function getExplain($sql)
    {
        return [];
    }

    protected function supportSavepoint()
    {
        return true;
    }
}
