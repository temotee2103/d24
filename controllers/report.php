<?php

class Report extends CI_Controller {

    /**
     * 用户报表
     */
    public function user() {
        if (!$this->checkLogin()) {
            redirect('login');
        }

        $this->load->model('user_model');
        
        // 获取用户统计数据
        $data = [
            'totalUsers' => $this->user_model->getTotalUsers(),
            'activeUsers' => $this->user_model->getActiveUsers(30),
            'newUsersToday' => $this->user_model->getNewUsersToday(),
            
            // 用户增长趋势
            'userGrowthData' => $this->user_model->getUserGrowthTrend(14),
            
            // 用户类型分布
            'userTypeData' => $this->user_model->getUserTypeDistribution(),
            
            // 最活跃用户
            'topActiveUsers' => $this->user_model->getTopActiveUsers(30, 10)
        ];
        
        $this->load->view('report/user', $data);
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