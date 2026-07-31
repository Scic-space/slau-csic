---
status: complete
phase: 06-question-bank-module
source:
  - .planning/phases/06-question-bank-module/06-01-SUMMARY.md
started: 2026-04-27T00:00:00.000Z
updated: 2026-04-27T12:00:00.000Z
---

## Current Test

number: 1
name: View question list
expected: |
  Admin navigates to /admin/questions and sees a paginated list of all questions. Each question displays its type (MCQ, True/False, Short Answer, Code Snippet) and marks value.
awaiting: user response

## Tests

### 1. View question list
expected: Admin navigates to /admin/questions and sees a paginated list of all questions. Each question displays its type (MCQ, True/False, Short Answer, Code Snippet) and marks value.
result: pass

### 2. Create MCQ question
expected: Admin clicks "Create Question", selects "MCQ" type, fills in question text, adds 4 options, marks 2 as correct, sets marks to 10, saves. Question appears in list.
result: pass

### 3. Create True/False question
expected: Admin creates question with "True/False" type. Form shows fixed True/False options. Admin selects "True" as correct, saves. Question shows in list as True/False type.
result: pass

### 4. Create Short Answer question
expected: Admin creates question with "Short Answer" type. No options field appears. Question saved with no options (for manual grading).
result: pass

### 5. Create Code Snippet question
expected: Admin creates question with "Code Snippet" type. Form shows code block textarea and language dropdown. Admin adds code and selects "javascript". Question saved with code and language.
result: pass

### 6. Edit question
expected: Admin clicks edit on an existing question. Form loads with all fields. Admin changes question text, updates marks, saves. Changes reflected in list.
result: pass

### 7. Soft-delete and restore question
expected: Admin deletes a question. Question disappears from list. Admin can restore (or view in trash). Soft delete working.
result: pass

### 8. Search questions
expected: Admin types in search box. List filters to show only questions containing search term in question text or options.
result: pass

### 9. Export to JSON
expected: Admin clicks "Export JSON" button. Browser downloads a .json file. File contains all questions in ExamShield format (type, question_text, marks, options, etc.)
result: pass

### 10. Non-admin access denied
expected: Non-admin user visits /admin/questions. Receives 403 Forbidden error. Access properly restricted.
result: pass

## Summary

total: 10
passed: 10
issues: 0
pending: 0
skipped: 0

## Gaps

[none yet]