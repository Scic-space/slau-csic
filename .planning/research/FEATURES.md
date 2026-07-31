# Feature Landscape

**Domain:** Question Bank Module for Club Management System
**Researched:** 2026-04-27
**Confidence:** HIGH

## Executive Summary

A Question Bank module enables organizations to create, organize, and reuse assessment questions across multiple quizzes, tests, and evaluations. For a club management system like SLAU CSIC, this would support certification programs, member assessments, training evaluations, and knowledge checks.

Key capabilities include: centralized question storage with categories and tags, multiple question types (MCQ, true/false, fill-in-the-blank), difficulty levels, bulk import/export, quiz creation with randomization, automated grading, and performance analytics.

---

## Table Stakes

Features users expect. Missing = product feels incomplete.

| Feature | Why Expected | Complexity | Notes |
|---------|--------------|------------|-------|
| **Question CRUD** | Create, read, update, delete questions | Medium | Core functionality - without this, nothing else works |
| **Question Categories** | Organize questions by subject/topic | Low | Hierarchical structure (Subject > Topic > Subtopic) |
| **Question Types** | Support multiple formats | Medium | MCQ, True/False, Fill-in-Blank are essential |
| **Question Bank View** | Central library of all questions | Low | Searchable, filterable list |
| **Quiz Creation** | Build assessments from question bank | Medium | Select specific or random questions |
| **Quiz Taking Interface** | Student-facing quiz experience | Medium | Question display, navigation, submission |
| **Auto-Grading** | Automatic scoring for objective questions | Medium | MCQ, True/False, Fill-in-Blank auto-graded |
| **Results/Scores** | View quiz results and scores | Low | Basic score display for students |

## Differentiators

Features that set product apart. Not expected, but valued.

| Feature | Value Proposition | Complexity | Notes |
|---------|-------------------|------------|-------|
| **Question Tags** | Flexible cross-cutting categorization beyond categories | Low | Adds searchability and filtering granularity |
| **Difficulty Levels** | Easy/Medium/Hard tagging for balanced assessments | Low | Enables skill-appropriate quiz assembly |
| **Randomized Questions** | Different question sets per attempt | Medium | Prevents cheating, increases replay value |
| **Question Pool Quiz** | Pull X random questions from category | Medium | Creates varied assessments from same bank |
| **Bulk Import** | Import questions from CSV/Excel | High | Saves massive time for initial population |
| **Question Versioning** | Track changes to questions over time | Medium | Maintains audit trail, allows rollback |
| **Usage Statistics** | Track how often questions are used | Low | Helps identify deprecated or overused questions |
| **Per-Question Analytics** | Correct/incorrect rates per question | Medium | Identifies confusing questions, informs improvement |
| **Quiz Time Limits** | Constrain quiz duration | Medium | Important for formal assessments |
| **Quiz Attempts** | Limit retry attempts | Low | Controls assessment behavior |
| **Explanation/Feedback** | Show correct answer after submission | Low | Improves learning outcome |
| **Hint System** | Optional hints during quiz | Low | Scaffolds learning |
| **Negative Marking** | Deduct points for wrong answers | Medium | Common in certification scenarios |
| **Export Questions** | Export to GIFT, CSV, JSON formats | Medium | Interoperability with other LMS |

## Anti-Features

Features to explicitly NOT build.

| Anti-Feature | Why Avoid | What to Do Instead |
|--------------|-----------|-------------------|
| **Essay Questions (v1)** | Requires manual grading - adds complexity without clear MVP need | Defer to Phase 2 if manual grading capacity exists |
| **Proctoring/Video Recording** | Privacy concerns, storage costs, complex implementation | Focus on randomization as anti-cheating mechanism |
| **Advanced Question Types (Matching, Ordering)** | High complexity, low immediate value | Defer to Phase 2 |
| **IP Restrictions** | Overkill for club/internal use | Use randomization instead |
| **Complex Branching/Learning Paths** | Beyond quiz scope - requires LMS integration | Keep focused on assessment only |
| **Full Gradebook Integration** | Adds dependency on member grading system | Keep quiz results standalone initially |

## Feature Dependencies

```plaintext
Question Bank → Quiz Creation → Quiz Taking → Results
     ↓              ↓              ↓
 Categories    Randomization   Auto-grading
 Tags          Time limits     Attempts
 Difficulty    Attempts        Feedback
```

**Dependency Chain:**
1. Categories/Tags must exist before questions can be organized
2. Questions must exist before quizzes can be created
3. Quiz configuration needs to be complete before students can take
4. Quiz submissions need auto-grading to produce immediate results
5. Results depend on completed quiz attempts

## MVP Recommendation

**Prioritize (Phase 1):**
1. Question CRUD with basic categories
2. Question types: MCQ, True/False, Fill-in-Blank
3. Simple Quiz Creation (manual question selection)
4. Quiz Taking with auto-grading
5. Basic Results view

**Defer (Phase 2):**
- Randomized question pools (requires more question volume)
- Bulk import (requires sample questions first)
- Per-question analytics (needs usage data first)
- Question versioning (nice-to-have, not critical)
- Export functionality (interoperability for later)
- Time limits, attempts configuration (can be added later)
- Difficulty levels (can be added as metadata)
- Tags (beyond categories)

**Rationale:** MVP focuses on core loop - create questions, build quiz, take quiz, see results. Everything else enhances but is not blocking.

---

## Sources

- Canvas LMS Question Bank documentation (2026)
- Moodle Question Bank documentation (2025)
- OpenEduCat Quiz Module documentation
- Testpress Question Bank feature documentation
- Quiz And Survey Master centralized question bank (2026)
- Equip Question Bank features (2026)
- QuestionForge question bank software documentation