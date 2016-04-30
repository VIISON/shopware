<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Shopware Log Controller
 *
 * This controller handles all actions made by the user or the server in the log module or the backend.
 * It reads all logs, creates new ones or deletes them.
 */
class Shopware_Controllers_Backend_Log extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Sets the ACL-rights for the log-module
     */
    public function initAcl()
    {
        $this->addAclPermission("getLogs", "read", "You're not allowed to see the logs.");
        $this->addAclPermission("deleteLogs", "delete", "You're not allowed to delete the logs.");
    }

    /**
     * Disable template engine for all actions
     *
     * @return void
     */
    public function preDispatch()
    {
        if (!in_array($this->Request()->getActionName(), array('index', 'load'))) {
            $this->Front()->Plugins()->Json()->setRenderer(true);
        }
    }

    /**
     * This function is called, when the user opens the log-module.
     * It reads the logs from s_core_log
     * Additionally it sets a filterValue
     */
    public function getBackendLogsAction()
    {
        $start = $this->Request()->get('start');
        $limit = $this->Request()->get('limit');

        //order data
        $order = (array) $this->Request()->getParam('sort', array());

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(
            'log.id as id',
            'log.type as type',
            'log.key as key',
            'log.text as text',
            'log.date as date',
            'log.user as user',
            'log.ipAddress as ip_address',
            'log.userAgent as user_agent',
            'log.value4 as value4'
        )->from('Shopware\Models\Log\Log', 'log');

        if ($filter = $this->Request()->get('filter')) {
            $filter = $filter[0];

            $builder->where('log.user LIKE ?1')
                    ->orWhere('log.text LIKE ?1')
                    ->orWhere('log.date LIKE ?1')
                    ->orWhere('log.ipAddress LIKE ?1')
                    ->orWhere('log.key LIKE ?1')
                    ->orWhere('log.type LIKE ?1');

            $builder->setParameter(1, '%'. $filter['value'] . '%');
        }
        $builder->addOrderBy($order);

        $builder->setFirstResult($start)->setMaxResults($limit);

        $result = $builder->getQuery()->getArrayResult();
        $total = Shopware()->Models()->getQueryCount($builder->getQuery());


        $this->View()->assign(array('success'=>true, 'data'=>$result, 'total'=>$total));
    }

    /**
     * This function is called when the user wants to delete a log.
     * It only handles the deletion.
     */
    public function deleteBackendLogsAction()
    {
        try {
            $params = $this->Request()->getParams();
            unset($params['module']);
            unset($params['controller']);
            unset($params['action']);
            unset($params['_dc']);

            if ($params[0]) {
                $data = array();
                foreach ($params as $values) {
                    $logModel = Shopware()->Models()->find('\Shopware\Models\Log\Log', $values['id']);

                    Shopware()->Models()->remove($logModel);
                    Shopware()->Models()->flush();
                    $data[] = Shopware()->Models()->toArray($logModel);
                }
            } else {
                $logModel = Shopware()->Models()->find('\Shopware\Models\Log\Log', $params['id']);

                Shopware()->Models()->remove($logModel);
                Shopware()->Models()->flush();
            }
            $this->View()->assign(array('success'=>true, 'data'=>$params));
        } catch (Exception $e) {
            $this->View()->assign(array('success'=>false, 'errorMsg'=>$e->getMessage()));
        }
    }

    /**
     * This method is called when a new log is made automatically.
     * It sets the different values and saves the log into s_core_log
     */
    public function createLogAction()
    {
        try {
            $params = $this->Request()->getParams();
            $params['key'] = html_entity_decode($params['key']);

            $logModel = new Shopware\Models\Log\Log;

            $userAgent = $_SERVER['HTTP_USER_AGENT'];
            if (empty($userAgent)) {
                $userAgent = 'Unknown';
            }
            $logModel->fromArray($params);
            $logModel->setDate(new \DateTime("now"));
            $logModel->setIpAddress(getenv("REMOTE_ADDR"));
            $logModel->setUserAgent($userAgent);

            Shopware()->Models()->persist($logModel);
            Shopware()->Models()->flush();

            $data = Shopware()->Models()->toArray($logModel);

            $this->View()->assign(array('success'=>true, 'data'=>$data));
        } catch (Exception $e) {
            $this->View()->assign(array('success'=>false, 'errorMsg'=>$e->getMessage()));
        }
    }

    /**
     * Responds the sorted and paginated entries from the 'core' log files.
     */
    public function getCoreLogsAction()
    {
        $start = $this->Request()->getParam('start', 0);
        $limit = $this->Request()->getParam('limit', 1000);
        $sort = $this->Request()->getParam('sort', []);

        // Parse log files
        $sortAscending = !empty($sort) && isset($sort[0]['direction']) && $sort[0]['direction'] === 'ASC';
        $result = $this->parseJsonLog('core', $start, $limit, $sortAscending);

        $this->View()->assign([
            'success' => true,
            'data' => $result,
            'total' => 0
        ]);
    }

    /**
     * Responds the sorted and paginated entries from the 'plugin' log files.
     */
    public function getPluginLogsAction()
    {
        $start = $this->Request()->getParam('start', 0);
        $limit = $this->Request()->getParam('limit', 1000);
        $sort = $this->Request()->getParam('sort', []);

        // Parse log files
        $sortAscending = !empty($sort) && isset($sort[0]['direction']) && $sort[0]['direction'] === 'ASC';
        $result = $this->parseJsonLog('plugin', $start, $limit, $sortAscending);

        $this->View()->assign([
            'success' => true,
            'data' => $result,
            'total' => 0
        ]);
    }

    /**
     * @param string $type
     * @param int $offset
     * @param int $limit
     * @param boolean $sortAscending
     * @return array
     */
    private function parseJsonLog($type, $offset, $limit, $sortAscending)
    {
        // Find all log files
        $logsDir = $this->container->getParameter('kernel.logs_dir');
        $environment = $this->container->getParameter('kernel.environment');
        $pattern = '/'.$type.'_'.$environment.'-.*\.log/';
        $files = scandir($logsDir, ($sortAscending) ? SCANDIR_SORT_ASCENDING : SCANDIR_SORT_DESCENDING);
        $logFiles = array_filter($files, function($fileName) use ($pattern) {
            return preg_match($pattern, $fileName, $matches) === 1;
        });

        // Pars log files
        $skipped = 0;
        $entries = [];
        foreach ($logFiles as $fileName) {
            // Read file line by line
            $handle = fopen($logsDir.'/'.$fileName, 'r');
            if (!$handle) {
                continue;
            }
            $lines = [];
            while (($line = fgets($handle)) !== false) {
                $lines[] = $line;
            }
            fclose($handle);

            if (!$sortAscending) {
                // Revers lines to read newest results first
                $lines = array_reverse($lines);
            }

            // Parse log lines
            foreach ($lines as $line) {
                if ($skipped < $offset) {
                    $skipped++;
                    continue;
                }

                // Try to parse JSON log
                $json = json_decode($line, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    continue;
                }
                // $json['timestamp'] = new DateTime($json['timestamp']);

                $entries[] = $json;
                if (count($entries) === $limit) {
                    break 2;
                }
            }
        }

        return $entries;
    }
}
