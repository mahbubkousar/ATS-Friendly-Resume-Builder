<?php

require_once __DIR__ . '/../repositories/DashboardRepository.php';

final class DashboardService
{
    private ?DashboardRepository $repository;

    public function __construct(?mysqli $connection)
    {
        $this->repository = $connection ? new DashboardRepository($connection) : null;
    }

    public function buildViewModel(array $sessionUser): array
    {
        $userId = (int) ($sessionUser['id'] ?? 0);
        $profile = $this->fallbackProfile($sessionUser);
        $resumes = [];
        $education = [];
        $experience = [];
        $applications = [];

        if ($this->repository !== null) {
            $profile = $this->repository->findUserProfile($userId) ?? $profile;
            $resumes = $this->repository->findResumes($userId);
            $education = $this->repository->findEducation($userId);
            $experience = $this->repository->findExperience($userId);
            $applications = $this->repository->findApplications($userId);
        }

        return [
            'userProfile' => $profile,
            'resumes' => $resumes,
            'education' => $education,
            'experience' => $experience,
            'applications' => $applications,
            'applicationStats' => $this->calculateApplicationStats($applications),
        ];
    }

    private function fallbackProfile(array $sessionUser): array
    {
        return [
            'full_name' => $sessionUser['fullname'] ?? '',
            'email' => $sessionUser['email'] ?? '',
            'phone' => '',
            'date_of_birth' => null,
            'address_line1' => '',
            'city' => '',
            'state' => '',
            'zip_code' => '',
            'country' => '',
            'professional_title' => '',
            'professional_summary' => '',
        ];
    }

    private function calculateApplicationStats(array $applications): array
    {
        $stats = [
            'total' => count($applications),
            'inProgress' => 0,
            'interviews' => 0,
            'offers' => 0,
        ];

        foreach ($applications as $application) {
            $status = $application['status'] ?? '';
            if (in_array($status, ['Applied', 'In Review'], true)) {
                $stats['inProgress']++;
            }
            if (in_array($status, ['Interview Scheduled', 'Interview Completed'], true)) {
                $stats['interviews']++;
            }
            if (in_array($status, ['Offer Received', 'Accepted'], true)) {
                $stats['offers']++;
            }
        }

        return $stats;
    }
}
