<?php declare(strict_types=1);


namespace Freelo;

use Carbon\Carbon;
use Exception;

class Client
{
    use ApiConnection, Validations;
    protected $apiKey;
    protected $endpointUrl = 'https://api.freelo.cz/v1/';
    protected $loginEmail;

    public function __construct(string $apiKey, string $loginEmail)
    {
        $this->apiKey = $apiKey;
        $this->loginEmail = $loginEmail;
    }

    /**
     * @throws Exception
     */
    public function createProject(string $projectName, string $currencyIso)
    {
        $this->currencyValidation($currencyIso);

        $body = [
            'name' => $projectName,
            'currency_iso' => strtoupper($currencyIso),
        ];

        return self::apiPostCall('projects', $body)->id;
    }

    /**
     * @throws Exception
     */
    public function getAllOwnProjectIncludinglToDo()
    {
        return self::apiGetCall('projects');
    }

    /**
     * @throws Exception
     */
    public function getAllInvitedProjects()
    {
        return self::apiGetCall('invited-projects');
    }

    /**
     * @throws Exception
     */
    public function getAllTemplateProjects()
    {
        return self::apiGetCall('template-projects');
    }

    /**
     * @throws Exception
     */
    public function getAllArchivededProjects()
    {
        return self::apiGetCall('archived-projects');
    }

    /**
     * @throws Exception
     */
    public function allProjectWorkers(int $projectId)
    {
        return self::apiGetCall('project/' . $projectId . '/workers');
    }

    /**
     * @throws Exception
     */
    public function createToDoList(int $projectId, int $budget, string $projectName)
    {
        $this->budgetValidation($budget);

        $body = [
            'name' => $projectName,
            'budget' => $budget,
        ];

        return self::apiPostCall('project/' . $projectId . '/tasklists', $body);
    }

    /**
     * @throws Exception
     */
    public function assignableWorkersCollection(int $projectId, int $taskId)
    {
        return $this->apiGetCall('project/' . $projectId . '/tasklist/' . $taskId . '/assignable-workers');
    }

    /**
     * @throws Exception
     */
    public function createTask(
        int $projectId,
        int $taskId,
        string $projectName,
        Carbon $dueDate,
        Carbon $dueDateEnd,
        int $worker,
        string $comment,
        array $labels = []
    )
    {
        $body = [
            'name' => $projectName,
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

        return self::apiPostCall('project/' . $projectId . '/tasklist/' . $taskId . '/tasks', $body);
    }

    /**
     * @throws Exception
     */
    public function getTasksInToDoList(int $projectId, int $taskId)
    {
        return $this->apiGetCall('project/' . $projectId . '/tasklist/' . $taskId . '/tasks');
    }

    /**
     * @throws Exception
     */
    public function getFinishedTasksInToDoList(int $tasklistId)
    {
        return $this->apiGetCall('tasklist/' . $tasklistId . '/finished-tasks')->data->finished_tasks;
    }

    /**
     * @throws Exception
     */
    public function getTasksByOwnTag(string $taskTag)
    {
        return $this->apiGetCall('tasks/tag/' . $taskTag);
    }

    /**
     * @throws Exception
     */
    public function getTask(int $taskId)
    {
        return $this->apiGetCall('task/' . $taskId);
    }

    /**
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
     * @throws Exception
     */
    public function getWorkReportsCollection(
        array $projectIds,
        array $userIds,
        Carbon $datereportedFrom,
        Carbon $dateReportedTo
    )
    {
        if (!$this->dueDateGreaterThanStartDate($dateReportedTo, $datereportedFrom)) {
            throw new Exception('End date must be greater then start date');
        }

        return 'TBD Freelo needs to fix the api';
    }

    /**
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
     * @throws Exception
     */
    public function getIssuedInvoicesCollection()
    {
        return $this->apiGetCall('issued-invoices')->data->issued_invoices;
    }

    /**
     * @throws Exception
     */
    public function getIssuedInvoiceDetail(int $invoiceId)
    {
        return $this->apiGetCall('issued-invoice/' . $invoiceId);
    }

    /**
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
     * @throws Exception
     */
    public function getCollectionOfAllUsers()
    {
        return $this->apiGetCall('users')->data->users;
    }

    /**
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
}
