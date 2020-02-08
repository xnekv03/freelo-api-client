<?php declare(strict_types=1);


namespace Freelo;

use Carbon\Carbon;
use Exception;

/**
 * Class Client
 * @package Freelo
 */
class Client
{
    use ApiConnection, Validations;
    /**
     * @var string
     */
    protected $apiKey;
    /**
     * @var string
     */
    protected $endpointUrl = 'https://api.freelo.cz/v1/';
    /**
     * @var string
     */
    protected $loginEmail;

    /**
     * Client constructor.
     * @param string $apiKey
     * @param string $loginEmail
     */
    public function __construct(string $apiKey, string $loginEmail)
    {
        $this->apiKey = $apiKey;
        $this->loginEmail = $loginEmail;
    }


    /**
     * @param string $projectName
     * @param string $currencyIso
     * @return mixed
     * @throws Exception
     */
    public function createProject(string $projectName, string $currencyIso)
    {
        $body = [
            'name' => $projectName,
            'currency_iso' => strtoupper($currencyIso),
        ];

        return self::apiPostCall('projects', $body)->id;
    }


    /**
     * @return mixed
     * @throws Exception
     */
    public function getAllOwnProjectIncludinglToDo()
    {
        return self::apiGetCall('projects');
    }


    /**
     * @return mixed
     * @throws Exception
     */
    public function getAllInvitedProjects()
    {
        return self::apiGetCall('invited-projects')->data->invited_projects;
    }


    /**
     * @return mixed
     * @throws Exception
     */
    public function getAllTemplateProjects()
    {
        return self::apiGetCall('template-projects')->data;
    }


    /**
     * @return mixed
     * @throws Exception
     */
    public function getAllArchivededProjects()
    {
        return self::apiGetCall('archived-projects')->data;
    }


    /**
     * @param int $projectId
     * @return mixed
     * @throws Exception
     */
    public function allProjectWorkers(int $projectId)
    {
        return self::apiGetCall('project/' . $projectId . '/workers')->data;
    }


    /**
     * @param int $projectId
     * @param float $budget
     * @param string $listName
     * @return mixed
     * @throws Exception
     */
    public function createToDoList(int $projectId, float $budget, string $listName)
    {
        $this->budgetValidation($budget);

        $body = [
            'name' => $listName,
            'budget' => $budget,
        ];

        return self::apiPostCall('project/' . $projectId . '/tasklists', $body);
    }


    /**
     * @param int $projectId
     * @param int $taskId
     * @return mixed
     * @throws Exception
     */
    public function assignableWorkersCollection(int $projectId, int $taskId)
    {
        return $this->apiGetCall('project/' . $projectId . '/tasklist/' . $taskId . '/assignable-workers');
    }


    /**
     * @param int $projectId
     * @param int $tasklistId
     * @param string $taskName
     * @param Carbon $dueDate
     * @param Carbon $dueDateEnd
     * @param int $worker
     * @param string $comment
     * @param array $labels
     * @return mixed
     * @throws Exception
     */
    public function createTask(
        int $projectId,
        int $tasklistId,
        string $taskName,
        Carbon $dueDate,
        Carbon $dueDateEnd,
        int $worker,
        string $comment,
        array $labels = []
    )
    {
        $body = [
            'name' => $taskName,
            'due_date' => $dueDate,
            'due_date_end' => $dueDateEnd,
            'worker' => $worker,
            'comment' => ['content' => $comment],
            'labels' => $labels
        ];

        $this->labelsArrayIsValid($labels);

        if (!$this->dueDateGreaterThanStartDate($dueDateEnd, $dueDate)) {
            throw new Exception('Due date end must be greater than due date');
        }
        return $this->apiPostCall('project/' . $projectId . '/tasklist/' . $tasklistId . '/tasks', $body);
    }


    /**
     * @param int $projectId
     * @param int $taskId
     * @return mixed
     * @throws Exception
     */
    public function getTasksInToDoList(int $projectId, int $taskId)
    {
        return $this->apiGetCall('project/' . $projectId . '/tasklist/' . $taskId . '/tasks');
    }


    /**
     * @param int $tasklistId
     * @return mixed
     * @throws Exception
     */
    public function getFinishedTasksInToDoList(int $tasklistId)
    {
        return $this->apiGetCall('tasklist/' . $tasklistId . '/finished-tasks')->data->finished_tasks;
    }


    /**
     * @param int $taskId
     * @return mixed
     * @throws Exception
     */
    public function getTask(int $taskId)
    {
        return $this->apiGetCall('task/' . $taskId);
    }


    /**
     * @param int $taskId
     * @param string $name
     * @param Carbon $due_date
     * @param Carbon $due_date_end
     * @param int $workerId
     * @return mixed
     * @throws Exception
     */
    public function editTask(int $taskId, string $name, Carbon $due_date, Carbon $due_date_end, int $workerId)
    {
        $body = [
            'name' => $name,
            'due_date' => $due_date,
            'due_date_end' => $due_date_end,
            'worker' => $workerId,
        ];

        if (!$this->dueDateGreaterThanStartDate($due_date_end, $due_date)) {
            throw new Exception('Due date end must be greater than due date');
        }

        return $this->apiPostCall('task/' . $taskId, $body);
    }


    /**
     * @param array $labels
     * @return mixed
     * @throws Exception
     */
    public function createTaskLabels(array $labels)
    {
        $this->labelsArrayIsValid($labels);

        $body = [
            'labels' => $labels
        ];

        return $this->apiPostCall('task-labels', $body);
    }


    /**
     * @param int $taskId
     * @param string $commentContent
     * @param array $files
     * @return mixed
     * @throws Exception
     */
    public function createComment(int $taskId, string $commentContent, array $files)
    {
        $this->filesArrayValidation($files);

        $body = [
            'content' => $commentContent,
            'files' => $files,
        ];

        return $this->apiPostCall('task/' . $taskId . '/comments', $body);
    }


    /**
     * @param int $taskId
     * @param Carbon $dateReported
     * @param int $workerId
     * @param int $minutes
     * @param string $cost
     * @param string $notice
     * @return mixed
     * @throws Exception
     */
    public function createWorkReport(
        int $taskId,
        Carbon $dateReported,
        int $workerId,
        int $minutes,
        string $cost,
        string $notice
    )
    {
        $body = [
            'date_reported' => $dateReported,
            'worker_id' => $workerId,
            'minutes' => $minutes,
            'cost' => $cost,
            'note' => $notice,
        ];
        return $this->apiPostCall('task/' . $taskId . '/work-reports', $body);
    }


    /**
     * @return mixed
     * @throws Exception
     */
    public function getIssuedInvoicesCollection()
    {
        return $this->apiGetCall('issued-invoices')->data->issued_invoices;
    }


    /**
     * @param int $invoiceId
     * @return mixed
     * @throws Exception
     */
    public function getIssuedInvoiceDetail(int $invoiceId)
    {
        return $this->apiGetCall('issued-invoice/' . $invoiceId);
    }


    /**
     * @param int $invoiceId
     * @param string $url
     * @param string $subject
     * @return mixed
     * @throws Exception
     */
    public function markIssuedInvoiceAsInvoiced(int $invoiceId, string $url, string $subject)
    {

        $body = [
            'url' => $this->validateUrl($url),
            'subject' => $subject,
        ];

        return $this->apiPostCall('issued-invoice/' . $invoiceId . '/mark-as-invoiced', $body);
    }


    /**
     * @return mixed
     * @throws Exception
     */
    public function getCollectionOfAllUsers()
    {
        return $this->apiGetCall('users')->data->users;
    }


    /**
     * @param array $projectsIds
     * @param array $emails
     * @return mixed
     * @throws Exception
     */
    public function inviteUsersByEmails(array $projectsIds, array $emails)
    {
        $this->validateIdsArrays($projectsIds);
        $this->validateEmailArray($emails);

        $body = [
            'projects_ids' => $projectsIds,
            'emails' => $emails,
        ];

        return $this->apiPostCall('users/manage-workers', $body);
    }


    /**
     * @param array $usersIds
     * @param array $projectsIds
     * @return mixed
     * @throws Exception
     */
    public function inviteUsersByUserIds(array $usersIds, array $projectsIds)
    {
        $this->validateIdsArrays($usersIds);
        $this->validateIdsArrays($projectsIds);

        $body = [
            'projects_ids' => $projectsIds,
            'users_ids' => $usersIds,
        ];

        return $this->apiPostCall('users/manage-workers', $body);
    }


    /**
     * @param int $projectId
     * @return mixed
     * @throws Exception
     */
    public function deleteProject(int $projectId)
    {
        return $this->apiDeleteCall('project/' . $projectId);
    }


    /**
     * @param array $projectIds
     * @throws Exception
     */
    public function deleteMultipleProjects(array $projectIds)
    {
        foreach ($projectIds as $id) {
            if (!is_int($id)) {
                throw new Exception('Project Id must be integer');
            }
        }

        // return $this->apiDeleteCall('project/' . $projectId);
    }


    /**
     * @param int $tasktId
     * @return mixed
     * @throws Exception
     */
    private function deleteTask(int $tasktId)
    {
        return $this->apiDeleteCall('task/' . $tasktId);
    }

    /**
     * @param array $projectIds
     * @throws Exception
     */
    public function deleteProjects(array $projectIds)
    {
        foreach ($projectIds as $id) {
            if (!is_int($id)) {
                throw new Exception('invalid project id');
            }
        }
    }
}
