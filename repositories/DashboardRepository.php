<?php

final class DashboardRepository
{
    private mysqli $connection;

    public function __construct(mysqli $connection)
    {
        $this->connection = $connection;
    }

    public function findUserProfile(int $userId): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT full_name, email, phone, date_of_birth, address_line1, city,
                    state, zip_code, country, professional_title, professional_summary
             FROM users
             WHERE user_id = ?'
        );
        $statement->bind_param('i', $userId);
        $statement->execute();
        $profile = $statement->get_result()->fetch_assoc() ?: null;
        $statement->close();

        return $profile;
    }

    public function findResumes(int $userId): array
    {
        return $this->findAllForUser(
            'SELECT resume_id, resume_title, template_name, status, updated_at
             FROM resumes
             WHERE user_id = ?
             ORDER BY updated_at DESC',
            $userId
        );
    }

    public function findEducation(int $userId): array
    {
        return $this->findAllForUser(
            'SELECT education_id, institution_name, degree, field_of_study,
                    start_date, end_date, gpa
             FROM user_education
             WHERE user_id = ?
             ORDER BY start_date DESC',
            $userId
        );
    }

    public function findExperience(int $userId): array
    {
        return $this->findAllForUser(
            'SELECT experience_id, job_title, company_name, location, start_date,
                    end_date, current_position, description
             FROM user_experience
             WHERE user_id = ?
             ORDER BY start_date DESC',
            $userId
        );
    }

    public function findApplications(int $userId): array
    {
        return $this->findAllForUser(
            'SELECT ja.application_id, ja.company_name, ja.job_title, ja.job_location,
                    ja.job_type, ja.salary_range, ja.application_date, ja.status,
                    ja.priority, ja.application_url, ja.notes, ja.resume_id,
                    r.resume_title
             FROM job_applications ja
             LEFT JOIN resumes r
               ON r.resume_id = ja.resume_id
              AND r.user_id = ja.user_id
             WHERE ja.user_id = ?
             ORDER BY ja.application_date DESC',
            $userId
        );
    }

    private function findAllForUser(string $query, int $userId): array
    {
        $statement = $this->connection->prepare($query);
        $statement->bind_param('i', $userId);
        $statement->execute();
        $result = $statement->get_result();
        $rows = [];

        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        $statement->close();
        return $rows;
    }
}
