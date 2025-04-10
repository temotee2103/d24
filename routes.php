// 报表
$router->get('/report/financial', 'ReportController@financial');
$router->get('/report/user', 'ReportController@user');
$router->get('/report/transactions', 'ReportController@transactions'); 