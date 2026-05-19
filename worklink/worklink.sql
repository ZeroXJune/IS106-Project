-- ============================================================
--  worklink.sql  — run once in phpMyAdmin or MySQL CLI
-- ============================================================
CREATE DATABASE IF NOT EXISTS worklink CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE worklink;

CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(150) NOT NULL,
    email      VARCHAR(191) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('applicant','employer') NOT NULL,
    headline   VARCHAR(255) DEFAULT NULL,
    location   VARCHAR(150) DEFAULT NULL,
    skills     TEXT         DEFAULT NULL,
    about      TEXT         DEFAULT NULL,
    company    VARCHAR(200) DEFAULT NULL,
    position   VARCHAR(150) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS jobs (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    employer_id INT          NOT NULL,
    title       VARCHAR(200) NOT NULL,
    company     VARCHAR(200) NOT NULL,
    location    VARCHAR(150) NOT NULL,
    type        ENUM('Full-time','Part-time','Internship','Contract') NOT NULL DEFAULT 'Full-time',
    salary      VARCHAR(100) DEFAULT NULL,
    description TEXT         NOT NULL,
    tags        VARCHAR(500) DEFAULT NULL,
    deadline    DATE         DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employer_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS applications (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    job_id       INT NOT NULL,
    applicant_id INT NOT NULL,
    cover_letter TEXT         DEFAULT NULL,
    resume_link  VARCHAR(500) DEFAULT NULL,
    phone        VARCHAR(30)  DEFAULT NULL,
    status       ENUM('Applied','Under Review','Interview','Offered','Rejected') NOT NULL DEFAULT 'Applied',
    applied_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id)       REFERENCES jobs(id)  ON DELETE CASCADE,
    FOREIGN KEY (applicant_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uq_application (job_id, applicant_id)
);

-- Seed demo accounts (password = "pass123")
INSERT INTO users (name, email, password, role, company, position) VALUES
('Rico Reyes',     'rico@techcorp.ph',  '$2y$10$FfoifyhQnLOiY8voIVsMhuE5zO7HsqS.D2grCFaSN8NVKYZIM17zS', 'employer', 'TechCorp PH',  'HR Manager'),
('Ana Lim',        'ana@bdo.com.ph',    '$2y$10$FfoifyhQnLOiY8voIVsMhuE5zO7HsqS.D2grCFaSN8NVKYZIM17zS', 'employer', 'BDO Unibank',  'Talent Acquisition'),
('Mark Dela Cruz', 'mark@globe.com.ph', '$2y$10$FfoifyhQnLOiY8voIVsMhuE5zO7HsqS.D2grCFaSN8NVKYZIM17zS', 'employer', 'Globe Telecom','Recruitment Lead');

INSERT INTO users (name, email, password, role, headline, location, skills, about) VALUES
('Maria Santos', 'maria@email.com', '$2y$10$FfoifyhQnLOiY8voIVsMhuE5zO7HsqS.D2grCFaSN8NVKYZIM17zS',
 'applicant', 'BS Information Systems | 3rd Year', 'Bacolod City', 'PHP,SQL,Bootstrap',
 'Passionate IS student looking for opportunities to grow.');

INSERT INTO jobs (employer_id, title, company, location, type, salary, description, tags, deadline) VALUES
(1,'Frontend Developer','TechCorp PH','Makati, Metro Manila','Full-time','₱35,000–₱50,000','Build responsive web interfaces. Collaborate with design and backend teams.','PHP,CSS,JavaScript','2026-06-01'),
(2,'Systems Analyst','BDO Unibank','Ortigas, Pasig','Full-time','₱30,000–₱45,000','Analyze and document business processes. Work with stakeholders on IT solutions.','Analysis,Documentation,SQL','2026-06-15'),
(1,'IT Support Specialist','TechCorp PH','BGC, Taguig','Full-time','₱22,000–₱28,000','Provide technical support and troubleshoot hardware/software issues.','Networking,Hardware,Windows','2026-05-30'),
(3,'Junior Data Analyst','Globe Telecom','Mandaluyong','Full-time','₱28,000–₱38,000','Collect and analyze datasets. Create dashboards and reports.','Excel,SQL,Power BI','2026-06-20'),
(3,'Web Developer Intern','Globe Telecom','Remote','Internship','₱500/day allowance','Assist in building web applications. Learn agile practices.','HTML,CSS,PHP','2026-05-25');
