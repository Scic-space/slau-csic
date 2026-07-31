# Phase 10: Learning & Training - Research

## Existing Infrastructure

The codebase already has training-related database infrastructure:

### Database Migrations
- `trainings` table - with title, slug, description, category (ethical_hacking, digital_forensics, etc.), difficulty (beginner/intermediate/advanced), objectives, prerequisites, duration_hours, resources (JSON), publication status
- `training_modules` table - linked to trainings with title, content, order, duration_minutes, resources (JSON)
- `training_enrollments` table - exists via model

### Models (Currently Empty Stubs)
- `Training` (App\Models\Training)
- `TrainingModule` (App\Models\TrainingModule)
- `ModuleProgress` (App\Models\ModuleProgress)
- `TrainingEnrollment` (App\Models\TrainingEnrollment)

### What Needs to Be Built
This phase needs to build out the full functionality since there's no UI or API layer yet.

## Key Integration Points

1. **Exam System (Phase 9)** - Can integrate with exams for skill assessments after training completion
2. **User Model** - Track progress per user, link to existing User model
3. **Existing Livewire Patterns** - Use similar patterns to Events, CTF Challenges for consistency

## Research Questions for Planning

1. **Learning Path Structure**
   - How to organize trainings into sequential learning paths?
   - Should paths have prerequisites (complete Training A before B)?

2. **Progress Tracking**
   - How to track module completion per user?
   - Percentage-based or checklist-based progress?
   - Progress persistence across sessions?

3. **Resource Library**
   - What resource types to support (PDF, video, links, code snippets)?
   - How to organize and search resources?

4. **Admin vs Member Views**
   - What can admins do (create/edit/delete paths, modules, view stats)?
   - What can members do (enroll, view, track progress)?

5. **Exam Integration**
   - Can training completion be a prerequisite for exam eligibility?
   - Should completion generate certificates?

## Architecture Pattern Recommendations

Based on existing codebase patterns (Events, CTF, Exams):

1. **Admin** - Use Filament resources or Livewire CRUD components
2. **Member UI** - Livewire pages with data tables, detail views
3. **API** - RESTful endpoints in app/Http/Controllers/Api/
4. **Models** - Already exist, need $fillable, relationships, methods
5. **Database** - Migrations exist, may need additional columns

## Validation Architecture

For Nyquist validation (Dimension 8), this phase should include:

1. **Functional Testing** - User can enroll, complete modules, see progress
2. **Progress Persistence** - Progress saved across sessions
3. **Admin Functionality** - Can create/edit/delete training content
4. **Integration** - Works with existing user system