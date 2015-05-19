<?php
/**
 * 给doctrine命令行工具调用的配置文件
 */

use Doctrine\ORM\Tools\Console\ConsoleRunner;

// replace with mechanism to retrieve EntityManager in your app
$entityManager = GetEntityManager();

return ConsoleRunner::createHelperSet($entityManager);
