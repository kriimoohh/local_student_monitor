# Changelog

All notable changes to the Student Monitor plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-11-17

### Added
- Initial release of Student Monitor plugin
- **Automated Notifications:**
  - Inactivity detection with 3 configurable levels (72h, 7d, 14d)
  - New pedagogical content notifications
  - Assignment deadline reminders (D-7, D-3, D-1, H-6)
  - Institutional announcements from designated forum
- **Manual Alerts:**
  - Supervisors can create custom alerts for exams, assignments, events
  - Support for scheduled reminders
  - Target audience selection (whole course, group, manual selection)
- **Student Tracking:**
  - Automatic risk level calculation (LOW, MEDIUM, HIGH, CRITICAL)
  - Inactivity days tracking
  - Missing assignments counter
  - Intervention flags and assignment to supervisors
  - Supervisor notes system
- **Multi-channel Delivery:**
  - Email notifications (native Moodle)
  - Moodle notifications system
  - SMS integration (via external API)
  - WhatsApp Business API integration
- **Dashboard & Reporting:**
  - Interactive supervisor dashboard
  - KPI cards (students at risk, notifications sent, interventions, read rate)
  - Student list with filtering capabilities
  - Statistics and charts
  - CSV export functionality
  - Weekly automated reports
- **Scheduled Tasks:**
  - check_inactivity: Every 6 hours
  - check_assignments_due: Daily at 1 AM
  - send_scheduled_notifications: Every 15 minutes
  - update_student_tracking: Daily at 2:30 AM
  - generate_weekly_report: Weekly on Monday at 8 AM
  - cleanup_old_logs: Monthly on 1st at 3 AM
- **Event Observers:**
  - Course module created/updated
  - Forum discussion created (institutional announcements)
  - Assignment submitted
  - User logged in
  - Course viewed
- **Database:**
  - 5 tables for notifications, tracking, config, logs, templates
  - Optimized indexes for performance
  - Default French templates installed
- **Security & Privacy:**
  - Full GDPR compliance via Privacy API
  - User data export capability
  - User data deletion capability
  - Granular capabilities system (8 permissions)
  - Action logging system
- **Customization:**
  - Configurable inactivity thresholds
  - Customizable message templates with placeholders
  - Per-course configuration options
  - Multi-language support (French & English)

### Technical Details
- Moodle version: 4.0+
- PHP version: 8.0+
- Database: PostgreSQL/MySQL compatible
- Follows Moodle coding standards
- Unit tests included
- Fully documented code (PHPDoc)

---

## [1.1.0] - 2025-11-17

### Added
- **Interactive Dashboard (dashboard.php)**
  - KPI cards: Students at risk, Notifications sent, Interventions needed, Read rate
  - Critical alerts section highlighting top 5 urgent cases
  - Filterable student list table with risk level badges
  - Quick action buttons for common tasks
  - Responsive Bootstrap-based design
- **Manual Alerts System**
  - Complete alert manager (alert_manager.php) for creating custom alerts
  - Alert form (manual_alert_form.php) with full validation
  - Alert types: Exam, Assignment, Announcement, Event
  - Target selection: All students, Course, Group, Manual selection
  - Multi-channel delivery configuration
  - Automatic reminder scheduling (D-7, D-3, D-1)
  - Alert statistics and tracking
- **Reporting & Export (reporting_manager.php)**
  - CSV export for students data with risk filtering
  - CSV export for notifications history
  - Weekly report generation with comprehensive stats
  - Notification trends data (for future charts)
  - Risk distribution data (for future visualization)
- **New Pages**
  - create_alert.php - Manual alert creation interface
  - view_alerts.php - Alert history viewer
  - export.php - Data export handler (CSV with UTF-8 BOM)
  - index.php - Entry point redirecting to dashboard
- **UI Enhancements**
  - Complete CSS stylesheet (styles/styles.css) with:
    - KPI card styling with hover effects
    - Risk level color coding
    - Responsive table and form styles
    - Mobile-responsive design
    - Print styles
  - JavaScript AMD module (amd/src/dashboard.js)
  - Bootstrap integration
- **Moodle Integration**
  - Message providers (db/messages.php)
  - Popup and email notification configuration
- **Localization**
  - 24 new language strings (French & English)
  - Form validation messages
  - UI element labels

### Technical Improvements
- Proper permission checks on all new pages
- Session key validation for forms
- Clean URL parameter handling
- Bootstrap-compatible HTML structure
- AMD module pattern for JavaScript
- CSV export with UTF-8 BOM for Excel compatibility

### Files Added (13)
1. dashboard.php
2. create_alert.php
3. view_alerts.php
4. export.php
5. index.php
6. classes/manager/alert_manager.php
7. classes/manager/reporting_manager.php
8. classes/form/manual_alert_form.php
9. db/messages.php
10. amd/src/dashboard.js
11. styles/styles.css

### Files Modified
- lang/en/local_student_monitor.php (+24 strings)
- lang/fr/local_student_monitor.php (+24 strings)

---

## [1.4.0] - 2025-11-17

### Added
- **PDF Export Manager (classes/manager/pdf_manager.php)**
  - Export students list to PDF with risk level filtering
  - Export detailed report with statistics and charts
  - Export notification history to PDF
  - Professional PDF formatting using TCPDF
  - Automatic file naming with timestamps
  - Color-coded risk levels in PDF tables
- **SMS Cost Tracker (classes/manager/sms_cost_tracker.php)**
  - Automatic cost tracking per SMS sent
  - Multi-part SMS calculation (160 char limit)
  - Monthly budget management and alerts
  - Cost statistics by period (week, month, year)
  - Daily cost trends for last 30 days
  - Cost breakdown by notification type
  - Budget limit enforcement (blocks SMS if exceeded)
  - Currency configuration (default: XOF for Senegal)
- **WhatsApp Template Support**
  - Send pre-approved WhatsApp Business templates
  - Template parameter substitution
  - French language support for UNCHK
  - Error handling and logging
- **Communication Statistics Page (communication_stats.php)**
  - Dedicated communication analytics dashboard
  - 4 KPI cards: Total SMS, Total cost, Avg cost, Budget status
  - Daily SMS costs chart (Chart.js)
  - Cost breakdown by notification type table
  - Channel distribution analysis
  - Period selector (week, month, year)
  - Budget percentage visual indicator
- **Communication Charts Module (amd/src/communication_charts.js)**
  - Daily SMS costs line chart
  - Responsive and interactive visualizations
  - Currency display in tooltips
  - Chart.js v4.4.0 integration
- **PDF Export Handler (export_pdf.php)**
  - Unified PDF export endpoint
  - Support for 3 export types: students, detailed, notifications
  - Parameter handling for filters and date ranges
  - Capability checking for security

### Technical Improvements
- Enhanced SMS sending with cost tracking integration
- Budget limit checking before SMS send
- WhatsApp template message API implementation
- TCPDF library integration for PDF generation
- Multi-part SMS calculation algorithm
- SQL queries for cost analytics
- Daily cost aggregation and trends

### Files Added (5)
1. classes/manager/pdf_manager.php - PDF export manager
2. classes/manager/sms_cost_tracker.php - SMS cost tracking
3. communication_stats.php - Communication statistics page
4. amd/src/communication_charts.js - Communication charts module
5. export_pdf.php - PDF export handler

### Files Modified
- version.php (updated to v1.4.0, version 2025111704)
- classes/manager/channel_manager.php (added SMS cost tracking and WhatsApp templates)
- lang/en/local_student_monitor.php (+35 strings)
- lang/fr/local_student_monitor.php (+35 strings)
- README.md (updated with Phase 5 features)

---

## [1.3.0] - 2025-11-17

### Added
- **Chart.js Integration (amd/src/charts.js)**
  - Interactive data visualization module
  - 4 chart types: Donut (risk distribution), Line (notification trends), Bar (notification types), Horizontal bar (interventions)
  - Responsive and customizable charts
  - CDN-based Chart.js loading (v4.4.0)
  - Color-coded risk levels matching badge colors
- **Advanced Reports Page (reports.php)**
  - Dedicated reporting page with comprehensive analytics
  - 4 interactive Chart.js visualizations
  - KPI summary cards (total students, notifications, at-risk, read rate)
  - Last 30 days notification trends
  - Export buttons for students and notifications
  - Professional layout with responsive grid
- **Bulk Actions System (bulk_actions.php)**
  - Multi-student action processing
  - 4 bulk actions: Assign to supervisor, Unassign, Add note, Send notification
  - Confirmation page before execution
  - Dynamic form fields based on action selection
  - Success/failure reporting
  - Session key validation for security
- **Template Editor (template_editor.php)**
  - Visual interface for editing notification templates
  - Edit subject and body for all template types
  - Available placeholders display with template-specific context
  - Reset to default functionality
  - Language support (FR templates)
  - Last modified tracking
- **Advanced Filtering (amd/src/advanced_filters.js)**
  - Real-time client-side filtering
  - 5 filter types: Search (name/email), Risk level, Inactivity days, Missing assignments, Assignment status
  - Clear filters button
  - Visible student count display
  - Bulk selection for visible students
  - Combined filter logic (AND operation)

### Technical Improvements
- AMD JavaScript modules for charts and filters
- Chart.js v4.4.0 integration via CDN
- Dynamic form field visibility with JavaScript
- Client-side filtering for instant results
- Responsive chart containers with fixed heights
- SQL queries optimized for trend data
- Color consistency across charts and badges

### Files Added (5)
1. amd/src/charts.js - Chart.js integration module
2. amd/src/advanced_filters.js - Advanced filtering module
3. reports.php - Advanced reports page
4. bulk_actions.php - Bulk actions page
5. template_editor.php - Template editor page

### Files Modified
- version.php (updated to v1.3.0, version 2025111703)
- lang/en/local_student_monitor.php (+60 strings)
- lang/fr/local_student_monitor.php (+60 strings)
- README.md (updated with Phase 4 features)

---

## [1.2.0] - 2025-11-17

### Added
- **Course-Specific Settings (course_settings.php)**
  - Per-course configuration page accessible from course menu
  - Course settings form (classes/form/course_settings_form.php)
  - Enable/disable Student Monitor for specific courses
  - Configurable activity types to monitor (assignments, quizzes, forums, resources, URLs, pages)
  - Custom assignment reminder days per course
  - Custom inactivity threshold per course
  - Default supervisor assignment per course
  - Teacher digest notifications (daily, weekly, monthly)
  - Settings stored in local_sm_config table with courseid
- **Student Notification Preferences (preferences.php)**
  - Self-service preferences page for students
  - Channel selection (Email, Moodle, SMS, WhatsApp)
  - Notification history viewer (last 10 notifications)
  - User preferences storage using Moodle's preferences API
  - Clean, accessible interface
- **Mustache Templates**
  - templates/kpi_card.mustache - Reusable KPI card component
  - templates/student_row.mustache - Student table row component
  - Improved code maintainability and consistency
  - Easier customization for themes
- **Unit Tests**
  - tests/notification_manager_test.php (5 test cases):
    - test_create_notification - Basic notification creation
    - test_create_inactivity_notification - Inactivity notification with template
    - test_replace_placeholders - Placeholder replacement logic
    - test_has_recent_notification - Recent notification checking
    - test_update_notification_status - Status transitions (pending → sent → read)
  - tests/student_tracker_test.php (6 test cases):
    - test_update_student_tracking - Tracking record creation
    - test_calculate_risk_level - All 4 risk levels (FAIBLE, MOYEN, ÉLEVÉ, CRITIQUE)
    - test_assign_to_supervisor - Supervisor assignment
    - test_add_notes - Note appending functionality
    - test_get_students_at_risk - Risk level filtering
    - test_get_statistics - Statistics calculation
  - All tests extend \advanced_testcase
  - Proper setUp() and resetAfterTest() usage
- **Localization**
  - 50+ new language strings (French & English)
  - Course settings vocabulary
  - Student preferences vocabulary
  - Complete bilingual support

### Technical Improvements
- Enhanced Moodle forms with conditional field display (hideIf)
- Dynamic supervisor list generation from course enrollments
- User preferences API integration
- Mustache template system for reusable UI components
- Comprehensive PHPUnit test coverage for core managers
- Improved code documentation

### Files Added (7)
1. course_settings.php
2. classes/form/course_settings_form.php
3. preferences.php
4. templates/kpi_card.mustache
5. templates/student_row.mustache
6. tests/notification_manager_test.php
7. tests/student_tracker_test.php

### Files Modified
- version.php (updated to v1.2.0, version 2025111702)
- lang/en/local_student_monitor.php (+50 strings)
- lang/fr/local_student_monitor.php (+50 strings)
- README.md (updated with Phase 3 features and comprehensive changelog)

---

## [1.5.0] - 2025-11-17

### Added
- **Workflow Automation System (classes/manager/workflow_manager.php)**
  - Automatic risk-based workflows for student interventions
  - execute_risk_workflows() - Main workflow orchestration method
  - execute_critical_workflow() - Urgent intervention for CRITIQUE risk level
    - Auto-assign to default supervisor
    - Create urgent intervention task (24h due date)
    - Send multi-channel notification (email, moodle, SMS)
    - Escalate to academic coordinator after 48h without response
  - execute_high_workflow() - Follow-up for ÉLEVÉ risk level
    - Auto-assign if inactivity > 7 days
    - Create follow-up task (3 days due date)
    - Send reminder if no contact in last 7 days
  - execute_medium_workflow() - Preventive for MOYEN risk level
    - Send preventive reminder if inactivity > 5 days
  - Automatic supervisor task creation with priority and due dates
  - Escalation to coordinator with detailed student information
  - Workflow execution logging
- **Task Management for Supervisors (tasks.php)**
  - Dedicated task management page for supervisors
  - 4 task KPI cards: Total, Pending, In progress, Overdue
  - Task filtering by status (all, pending, in_progress, completed)
  - Task priority system (urgent, high, normal, low)
  - Task types (urgent_intervention, follow_up, preventive, check_in)
  - Due date tracking with overdue highlighting
  - Quick actions: Start work, Mark complete, View details
  - Student context for each task (risk level, inactivity days, email)
  - Task table with sortable columns and status badges
  - Overdue task visual alerts
- **Intervention Tracking System (classes/manager/intervention_tracker.php)**
  - log_intervention() - Log all supervisor interventions
  - complete_task() - Mark tasks as completed with notes
  - defer_task() - Postpone tasks with new due dates
  - reassign_task() - Transfer tasks to other supervisors
  - get_intervention_history() - Student intervention timeline
  - get_supervisor_statistics() - Supervisor performance metrics
  - get_effectiveness_metrics() - Intervention success tracking
  - get_escalation_history() - Critical case escalations
  - Automatic student tracking updates after interventions
  - Intervention count tracking per student
  - Average response time calculation
- **Business Rules Engine (classes/manager/business_rules_engine.php)**
  - Customizable workflow automation rules
  - evaluate_rules() - Rule evaluation for students
  - 5 default business rules:
    1. Critical inactivity auto-assign
    2. High risk follow-up
    3. Escalate after 48h no response
    4. Missing assignments alert
    5. Budget limit warning (SMS)
  - Rule conditions with operators (==, !=, >, >=, <, <=, in, not_in)
  - Rule actions: assign_supervisor, create_task, send_notification, escalate_to_coordinator, notify_supervisor, disable_sms, notify_admin
  - execute_actions() - Execute rule-defined actions
  - create_rule() - Create custom rules
  - test_rule() - Test rules against current data
  - Priority-based rule execution
  - Rule execution logging
  - Calculated fields (hours_since_intervention, response_received, sms_budget_usage)
- **Effectiveness Reports (effectiveness.php)**
  - Comprehensive intervention effectiveness analytics
  - Period selector (week, month, quarter, year)
  - Supervisor filter for individual performance
  - 4 effectiveness KPIs:
    - Students improved (risk level decreased)
    - Students at risk (still critical/high)
    - Success rate percentage
    - Average interventions per student
  - Supervisor performance metrics:
    - Tasks completed
    - Tasks pending
    - Tasks overdue
    - Average response time (hours)
  - Risk transition analysis (improved, stable, deteriorated)
  - Risk transition donut chart (Chart.js)
  - Intervention type distribution table
  - PDF export button for effectiveness reports
- **JavaScript Modules**
  - amd/src/task_manager.js - Task management interface enhancements
    - Real-time status filtering
    - Action confirmations
    - Overdue task highlighting
    - Task statistics summary
    - Quick action button states
  - amd/src/effectiveness_charts.js - Effectiveness visualization
    - Risk transition donut chart
    - Responsive chart rendering
    - Percentage calculation in tooltips
- **Enhanced Settings**
  - default_supervisor_id - Default supervisor for auto-assignments
  - coordinator_email - Coordinator email for escalations
  - sms_monthly_budget - Monthly SMS budget limit

### Technical Improvements
- Automatic workflow execution based on risk levels
- Task management system with priorities and due dates
- Intervention tracking with complete history
- Business rules engine with customizable conditions
- Effectiveness metrics calculation
- Risk level transition tracking
- Average response time calculations
- Multi-condition rule evaluation
- Automatic escalation logic
- Budget enforcement for SMS

### Files Added (8)
1. classes/manager/workflow_manager.php - Workflow automation
2. classes/manager/intervention_tracker.php - Intervention tracking
3. classes/manager/business_rules_engine.php - Business rules engine
4. tasks.php - Task management page
5. effectiveness.php - Effectiveness reports page
6. amd/src/task_manager.js - Task management module
7. amd/src/effectiveness_charts.js - Effectiveness charts module

### Files Modified
- version.php (updated to v1.5.0, version 2025111705)
- lang/en/local_student_monitor.php (+113 strings)
- lang/fr/local_student_monitor.php (+113 strings)
- CHANGELOG.md (added Phase 6 documentation)

---

## [Unreleased]

### Planned for Future Versions
- [ ] JavaScript charts integration (Chart.js)
- [ ] Advanced filtering and search in student list
- [ ] Bulk actions for students
- [ ] Email template editor in admin interface
- [ ] PDF export
- [ ] SMS cost tracking
- [ ] WhatsApp template messages support

### Ideas for Future Releases
- Chatbot integration for student support
- AI-powered risk prediction
- Integration with student information systems
- Custom report builder
- Email campaign manager
- A/B testing for notification effectiveness
- Gamification for student engagement
- SMS two-way communication
- WhatsApp chatbot for FAQs
- Integration with video conferencing platforms

---

## Version History

- **v1.5.0** (2025-11-17) - Workflow Automation & Task Management
- **v1.4.0** (2025-11-17) - PDF Export & Communication Management
- **v1.3.0** (2025-11-17) - Visualization & Advanced Reporting
- **v1.2.0** (2025-11-17) - Configuration & Testing
- **v1.1.0** (2025-11-17) - UI, Dashboard & Manual Alerts
- **v1.0.0** (2025-11-17) - Initial release

---

## Contributors

- UNCHK Development Team
- Université Numérique Cheikh Hamidou Kane, Sénégal

---

## License

GNU GPL v3 or later

---

For more information, see README.md
