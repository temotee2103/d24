<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * 获取用户总数
     * @return int 用户总数
     */
    public function getTotalUsers() {
        return $this->db->count_all('users');
    }

    /**
     * 获取活跃用户数
     * @param int $days 过去几天
     * @return int 活跃用户数
     */
    public function getActiveUsers($days = 30) {
        $this->db->select('COUNT(DISTINCT user_id) as active')
                 ->from('user_logins')
                 ->where('login_time >=', date('Y-m-d H:i:s', strtotime("-$days days")));
        $result = $this->db->get()->row();
        return $result ? $result->active : 0;
    }

    /**
     * 获取今日新增用户数
     * @return int 今日新增用户数
     */
    public function getNewUsersToday() {
        $today = date('Y-m-d');
        $this->db->where('DATE(created_at) =', $today);
        return $this->db->count_all_results('users');
    }

    /**
     * 获取用户增长趋势
     * @param int $days 天数
     * @return array 用户增长数据
     */
    public function getUserGrowthTrend($days = 14) {
        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $this->db->select('COUNT(*) as count')
                     ->from('users')
                     ->where('DATE(created_at) =', $date);
            $query = $this->db->get()->row();
            $result[] = [
                'date' => date('m-d', strtotime($date)),
                'count' => (int)($query->count ?? 0)
            ];
        }
        return $result;
    }

    /**
     * 获取用户类型分布
     * @return array 用户类型分布数据
     */
    public function getUserTypeDistribution() {
        $this->db->select('role, COUNT(*) as count')
                 ->from('users')
                 ->group_by('role');
        $query = $this->db->get();
        
        $result = [];
        foreach ($query->result() as $row) {
            $result[] = [
                'type' => $this->getUserRoleName($row->role),
                'count' => (int)$row->count
            ];
        }
        return $result;
    }

    /**
     * 获取最活跃用户列表
     * @param int $days 天数范围
     * @param int $limit 限制数量
     * @return array 活跃用户列表
     */
    public function getTopActiveUsers($days = 30, $limit = 10) {
        $this->db->select('u.id, u.username, COUNT(l.id) as login_count, 
                          (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count,
                          (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE user_id = u.id) as total_spent,
                          MAX(l.login_time) as last_login')
                 ->from('users u')
                 ->join('user_logins l', 'u.id = l.user_id', 'left')
                 ->where('l.login_time >=', date('Y-m-d H:i:s', strtotime("-$days days")))
                 ->group_by('u.id')
                 ->order_by('login_count', 'DESC')
                 ->limit($limit);
        return $this->db->get()->result_array();
    }

    /**
     * 获取用户角色名称
     * @param string $roleCode 角色代码
     * @return string 角色名称
     */
    private function getUserRoleName($roleCode) {
        $roles = [
            'admin' => '管理员',
            'user' => '普通用户',
            'vip' => 'VIP用户',
            'seller' => '商家',
            'guest' => '访客'
        ];
        
        return $roles[$roleCode] ?? '未知';
    }
} 