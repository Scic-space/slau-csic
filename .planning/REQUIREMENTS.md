# Requirements: v1.1 Question Bank Module

## Categories

### QB — Question Bank Core

- [ ] **QB-01**: Admin can view list of all questions in the bank
- [ ] **QB-02**: Admin can create a new question with type selection
- [ ] **QB-03**: Admin can edit existing questions
- [ ] **QB-04**: Admin can delete (soft delete) questions
- [ ] **QB-05**: Admin can search questions by text content

### QT — Question Types

- [ ] **QT-01**: Support MCQ type with 2-6 options and multiple correct answers
- [ ] **QT-02**: Support True/False type with fixed 2 options
- [ ] **QT-03**: Support Short Answer type with no options (manual grading)
- [ ] **QT-04**: Support Code Snippet type with code block and language selection
- [ ] **QT-05**: Each question has marks/points value

### EXP — Export

- [ ] **EXP-01**: Export questions to JSON format
- [ ] **EXP-02**: JSON export follows ExamShield import format
- [ ] **EXP-03**: Export includes all question types and options

### PERM — Permissions

- [ ] **PERM-01**: Only admin users can access question bank
- [ ] **PERM-02**: Regular members cannot view or modify questions

---

## Phase 9 Requirements

### EXAM — Exam Core
- [ ] **EXAM-01**: Admin can create exams with title, description, duration, passing score, and status
- [ ] **EXAM-02**: Admin can edit exam details (title, description, duration, passing score)
- [ ] **EXAM-03**: Admin can publish/unpublish exams (status toggle)
- [ ] **EXAM-04**: Admin can delete exams (soft delete)
- [ ] **EXAM-05**: Admin can view paginated list of all exams with status and stats

### EQ — Exam Questions
- [ ] **EQ-01**: Admin can add questions to exam from Question Bank
- [ ] **EQ-02**: Admin can set custom marks/points per question in exam context
- [ ] **EQ-03**: Admin can reorder questions within an exam
- [ ] **EQ-04**: Admin can remove questions from exam
- [ ] **EQ-05**: Exam displays total marks based on selected questions

### ATTEMPT — Exam Attempts
- [ ] **ATT-01**: Members can view available (published) exams
- [ ] **ATT-02**: Members can start an exam attempt (creates attempt record with start time)
- [ ] **ATT-03**: Timer counts down and auto-submits when time expires
- [ ] **ATT-04**: Members can answer questions (MCQ, True/False, Short Answer, Code Snippet)
- [ ] **ATT-05**: Members can submit exam before timer expires
- [ ] **ATT-06**: Each user can have only one attempt per exam (unique constraint)

### GRADING — Exam Grading
- [ ] **GRAD-01**: MCQ and True/False answers auto-graded against correct options
- [ ] **GRAD-02**: Short answer questions sent to AI service for grading
- [ ] **GRAD-03**: Code snippet answers evaluated (manual grading or automated)
- [ ] **GRAD-04**: Total score calculated and stored
- [ ] **GRAD-05**: Pass/fail determined based on exam passing score

### RESULTS — Results Display
- [ ] **RES-01**: Members can view their exam results (score, pass/fail, answers review)
- [ ] **RES-02**: Admin can view all exam submissions with scores
- [ ] **RES-03**: Admin can manually adjust grades if needed
- [ ] **RES-04**: Results show correct/incorrect answers with explanations

### CERT — Certificate Eligibility
- [ ] **CERT-01**: Exam pass records certificate eligibility
- [ ] **CERT-02**: Members can see certificate eligibility status on profile
- [ ] **CERT-03**: Admin can view all certificate-eligible members per exam

### PERM-EXAM — Permissions
- [ ] **PERM-EXAM-01**: Only admin can create/edit/delete exams
- [ ] **PERM-EXAM-02**: Members can only view and attempt published exams
- [ ] **PERM-EXAM-03**: Members can only see their own results

---

## Future Requirements (Deferred)

- Quiz creation from question bank
- Quiz taking with timed attempts
- Randomized question pools
- Bulk import (CSV)
- Per-question analytics

---

## Out of Scope

- Student quiz taking interface
- Auto-grading functionality
- Question categories/taxonomy
- Version history

---

## Traceability

| Requirement | Phase | Status |
|-------------|-------|--------|
| QB-01 | Phase 6 | Pending |
| QB-02 | Phase 6 | Pending |
| QB-03 | Phase 6 | Pending |
| QB-04 | Phase 6 | Pending |
| QB-05 | Phase 6 | Pending |
| QT-01 | Phase 6 | Pending |
| QT-02 | Phase 6 | Pending |
| QT-03 | Phase 6 | Pending |
| QT-04 | Phase 6 | Pending |
| QT-05 | Phase 6 | Pending |
| EXP-01 | Phase 6 | Pending |
| EXP-02 | Phase 6 | Pending |
| EXP-03 | Phase 6 | Pending |
| PERM-01 | Phase 6 | Pending |
| PERM-02 | Phase 6 | Pending |
| EXAM-01 | Phase 9 | Pending |
| EXAM-02 | Phase 9 | Pending |
| EXAM-03 | Phase 9 | Pending |
| EXAM-04 | Phase 9 | Pending |
| EXAM-05 | Phase 9 | Pending |
| EQ-01 | Phase 9 | Pending |
| EQ-02 | Phase 9 | Pending |
| EQ-03 | Phase 9 | Pending |
| EQ-04 | Phase 9 | Pending |
| EQ-05 | Phase 9 | Pending |
| ATT-01 | Phase 9 | Pending |
| ATT-02 | Phase 9 | Pending |
| ATT-03 | Phase 9 | Pending |
| ATT-04 | Phase 9 | Pending |
| ATT-05 | Phase 9 | Pending |
| ATT-06 | Phase 9 | Pending |
| GRAD-01 | Phase 9 | Pending |
| GRAD-02 | Phase 9 | Pending |
| GRAD-03 | Phase 9 | Pending |
| GRAD-04 | Phase 9 | Pending |
| GRAD-05 | Phase 9 | Pending |
| RES-01 | Phase 9 | Pending |
| RES-02 | Phase 9 | Pending |
| RES-03 | Phase 9 | Pending |
| RES-04 | Phase 9 | Pending |
| CERT-01 | Phase 9 | Pending |
| CERT-02 | Phase 9 | Pending |
| CERT-03 | Phase 9 | Pending |
| PERM-EXAM-01 | Phase 9 | Pending |
| PERM-EXAM-02 | Phase 9 | Pending |
| PERM-EXAM-03 | Phase 9 | Pending |

---

*Generated: 2026-04-27*