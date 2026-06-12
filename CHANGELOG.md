# Changelog

All notable changes to the Student Monitor plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.0.6] - 2026-02-11

### Fixed
- Fix `Undefined property: stdClass::$template_type` in `template_editor.php` — DB column is `type`, not `template_type`. All 7 property accesses replaced.

---

## [3.0.5] - 2026-02-11

### Added
- Add 11 missing database tables to `install.xml` and `upgrade.php`: `local_sm_report_schedules`, `local_sm_campaigns`, `local_sm_campaign_recipients`, `local_sm_parents`, `local_sm_tasks`, `local_sm_achievements`, `local_sm_gamification`, `local_sm_interventions`, `local_sm_sms_costs`, `local_sm_goals`, `local_sm_custom_reports`
- Add missing lang strings for notification types: `manual_alert`, `assignment_reminder`, `new_content`, `forum_announcement`

### Fixed
- Fix `Unknown column 'st.last_intervention'` error in `intervention_tracker.php` — column does not exist, replaced with `timeupdated`
- Fix `Table 'local_sm_report_schedules' doesn't exist` and similar errors for 10 other tables missing from database schema
- Fix `Invalid get_string() identifier: 'manual_alert'` in `reports.php` — added missing lang strings for DB notification types
- Fix `Field 'lang' doesn't exist` in `template_editor.php` — correct field name is `language`, also fix sort column `template_type` → `type`

---

## [3.0.4] - 2026-02-09

### Fixed
- Fix `Undefined property: stdClass::$fullname` error in `bulk_actions.php` — use `fullname($student)` instead of `$student->fullname`
- Fix same issue in `pdf_manager.php` where `$student->fullname` was used instead of `fullname($student)`

---

## [3.0.3] - 2026-02-09

### Fixed
- Fix remaining missing user name fields in `tasks.php`, `custom_report_builder.php`, `manual_alert_form.php`, `alert_manager.php` (CSV parsing), `business_rules_engine.php`
- Replace direct `firstname . ' ' . lastname` concatenation with `fullname()` in `tasks.php`, `reporting_manager.php`, `pdf_manager.php`, `custom_report_builder.php`, `gamification_manager.php`
- Localize all hardcoded French text in `weekly_report.php` using `get_string()` calls
- Fix `weekly_report.php` using deprecated French risk level properties (`$stats->critique` etc.) — now uses English (`$stats->critical`)
- Localize hardcoded French CSV export headers in `reporting_manager.php`
- Add back-to-dashboard button on `report_schedules.php`

---

## [3.0.2] - 2026-02-07

### Fixed
- Fix "missing name fields from user object" warnings when calling `fullname()` across all pages and managers
- Add `firstnamephonetic`, `lastnamephonetic`, `middlename`, `alternatename` to all SQL queries and API calls that select user data
- Affected files: dashboard, student_tracker, reporting_manager, report_scheduler, alert_manager, gamification_manager, pdf_manager, search_users, check_inactivity, check_assignments_due, course_settings_form, refresh_tracking, bulk_actions, effectiveness

---

## [3.0.1] - 2026-02-07

### Fixed
- Fix "Duplicate value found in column userid" errors on Predictions page when students are tracked across multiple courses
- `get_all_predictions()`: use `SELECT DISTINCT userid` to avoid duplicate keys
- `get_student_history()` and `get_early_warnings()`: use `LIMIT 1` with worst-case ordering instead of `get_record()`

---

## [3.0.0] - 2026-02-07

### Added
- **Simplified risk calculation**: Direct threshold-based risk levels (no more scoring system)
  - `risk_level::from_criteria()` — "highest criterion wins" between inactivity and missing activities
  - Configurable thresholds for missing activities (defaults: 1/3/5)
- **Track all course activities**: Uses Moodle's completion tracking + assign submissions + quiz attempts (not just assignments)
- **Database migration**: Automatic field rename `missing_assignments` → `missing_activities` and French-to-English risk level migration

### Changed
- Risk levels now use English constants: LOW, MEDIUM, HIGH, CRITICAL (previously FAIBLE, MOYEN, ÉLEVÉ, CRITIQUE)
- `missing_assignments` renamed to `missing_activities` throughout the entire codebase
- `notification_count` no longer affects risk level calculation
- Admin settings: replaced risk score thresholds with missing activities thresholds

### Removed
- **Business Intelligence module**: `bi_dashboard.php`, `bi_analytics_engine.php`, `bi_charts.js` and all BI-related references
- Score-based risk calculation (`from_score()` method and `DEFAULT_THRESHOLD_*` constants)
- Obsolete config keys (`threshold_critical`, `threshold_high`, `threshold_medium`)

---

## [2.1.0] - 2025-11-19

### Added
- **Automatic Alerts Configuration Page (configure_automatic_alerts.php):**
  - Dedicated configuration interface for automatic alert system
  - Enable/disable automatic alerts with visual status indicators
  - Configure inactivity thresholds (Level 1: 3+ days, Level 2: 7+ days, Level 3: 14+ days)
  - Configure notification channels for automatic alerts (email, Moodle, SMS, WhatsApp)
  - Confirmation dialog for disabling automatic alerts to prevent accidental changes
  - Real-time status display on dashboard for administrators
  - Color-coded status cards (green for enabled, yellow for disabled)
- **Manual Alert Recipients by Inactivity/Risk Level:**
  - New recipient type: "By Inactivity / Risk Level" in manual alert form
  - 6 selection options:
    - Inactivity Level 1 (3+ days)
    - Inactivity Level 2 (7+ days)
    - Inactivity Level 3 (14+ days)
    - Risk Level CRITIQUE (critical students)
    - Risk Level ÉLEVÉ (high risk students)
    - Risk Level MOYEN (medium risk students)
  - Real-time student count display for each risk level
  - Student preview functionality (interface prepared)
  - Intelligent SQL queries to fetch students by level
  - Students ordered by inactivity (most inactive first)
- **Dashboard Integration:**
  - Automatic alerts status card on main dashboard
  - Visible only to users with managesettings capability
  - Quick access button to configuration page
  - Clear messaging about system status and impact
  - Color-coded visual indicators for enabled/disabled state
- **Enhanced Alert Manager:**
  - Support for inactivity level recipients in create_manual_alert
  - Day-based threshold selection (3, 7, 14 days)
  - Risk-based selection (CRITIQUE, ÉLEVÉ, MOYEN)
  - Dynamic threshold configuration based on admin settings
  - Comprehensive recipient filtering and validation

### Technical Improvements
- Enhanced manual_alert_form.php with inactivity level selection
- Extended alert_manager.php to handle new recipient type
- Added automatic alerts configuration persistence
- Dashboard status widget with permission checks
- Form validation for inactivity level selection
- Help text and contextual hints for administrators
- SQL optimization for large-scale recipient queries

### Files Added (1)
1. configure_automatic_alerts.php - Automatic alerts configuration page (319 lines)

### Files Modified
- classes/form/manual_alert_form.php (+37 lines)
- classes/manager/alert_manager.php (+49 lines)
- dashboard.php (+19 lines)
- lang/en/local_student_monitor.php (+34 strings)
- lang/fr/local_student_monitor.php (+34 strings)

### Localization
- 68 new language strings (34 French, 34 English)
- Automatic alerts configuration terminology
- Inactivity level selection labels
- Status messages and confirmations
- Help text for administrators

### User Experience
- Simplified automatic alerts management
- No code changes needed for threshold configuration
- Clear visual feedback on system status
- Targeted alert sending by risk/inactivity level
- Preview of affected students before sending
- Consistent terminology across interface

---

## [2.0.0] - 2025-11-19

### Added
- **Students at Risk Page (students_at_risk.php):**
  - Dedicated comprehensive page for viewing and filtering at-risk students
  - Interactive statistics cards for each risk level (CRITIQUE, ÉLEVÉ, MOYEN, FAIBLE)
  - Real-time student count display for each level
  - Click-to-filter functionality on statistics cards
  - Advanced sorting capabilities:
    - Sort by name (alphabetical)
    - Sort by email
    - Sort by risk level
    - Sort by inactivity days
    - Sort by missing assignments
    - Sort by notification count
  - Flexible pagination (25, 50, 100, 200 students per page)
  - Visual risk indicators with color-coded badges
  - Icon-based warning system:
    - Red circle (🔴) for 14+ days of inactivity
    - Orange circle (🟠) for 7-13 days of inactivity
    - Exclamation (❗) for 5+ missing assignments
    - Warning (⚠️) for 3-4 missing assignments
  - Profile pictures in student list
  - Quick actions per student:
    - View full profile (opens in new tab)
    - Send notification (direct link to alert form)
  - Current filter display with clear/reset option
  - Export to CSV with applied filters
  - Direct navigation from main dashboard
- **Navigation Integration:**
  - Added to Site Administration menu
  - Quick access button on main dashboard (danger/warning styled)
  - Breadcrumb navigation support
  - Proper capability checks (viewdashboard permission)
- **Enhanced Filtering:**
  - Filter by specific risk level or show all
  - Ascending/descending sort toggle
  - Persistent filter state across page loads
  - Clear visual indication of active filters
  - Student count display with current filter
- **Data Visualization:**
  - Color-coded risk level badges (danger, warning, info, success)
  - Responsive table design with hover effects
  - Mobile-friendly layout with profile pictures
  - Visual hierarchy for quick scanning
  - Font weight emphasis for critical metrics

### Technical Improvements
- Efficient SQL queries with pagination
- Dynamic sorting with SQL ORDER BY
- Parameterized queries for security
- User capability verification
- URL parameter handling for filters
- HTML table with sortable headers
- Bootstrap integration for responsive design
- Profile picture rendering with Moodle API
- UTF-8 encoding for international names

### Files Added (1)
1. students_at_risk.php - Dedicated students at risk page (370 lines)

### Files Modified
- settings.php (+11 lines) - Added navigation menu entry
- dashboard.php (+3 lines) - Added quick access button
- lang/en/local_student_monitor.php (+8 strings)
- lang/fr/local_student_monitor.php (+8 strings)

### Localization
- 16 new language strings (8 French, 8 English)
- Student at risk terminology
- Filter and navigation labels
- Success/empty state messages
- Action button labels

### User Experience
- Single-click access to filtered student lists
- Visual feedback for sorting direction
- Intuitive statistics overview
- Quick actions without leaving page
- Clear messaging about data state
- Consistent design with existing dashboard

---

## [1.9.0] - 2025-11-17

### Added
- **Advanced BI Analytics Engine:** Comprehensive business intelligence analytics with institutional overview, trend analysis, supervisor performance tracking, cohort analysis, retention analytics
- **BI Dashboard for Administrators:** Interactive dashboard with real-time KPIs, risk distribution visualization, daily interventions/success rate trends, retention metrics, supervisor performance tables, cohort analysis tables
- **Automated Report Scheduler:** Schedule reports (executive summary, supervisor performance, student risk, retention, cohort analysis) with multiple frequencies (daily, weekly, monthly, quarterly) and formats (PDF, CSV, HTML)
- **Report Scheduler Management Page:** Create, enable/disable, and delete scheduled reports with recipients configuration
- **BI Charts Module:** Chart.js visualizations for interventions trend, success rate trend, dual-axis retention trend chart
- **Institutional Metrics:** Total students, needs intervention count, success rate calculation, average response time, active supervisors count
- **Trend Analysis:** Daily risk distribution, intervention counts, notification counts, success rate trends over weeks
- **Cohort Analysis:** Group students by course, enrolment period, or risk level with detailed metrics
- **Retention Analytics:** Overall retention rate, at-risk dropout count, dropout prediction, retention trends over 12 weeks, retention by risk level
- **Supervisor Performance Analytics:** Assigned students, total interventions, average response time, students improved, success rate per supervisor
- **Automated Report Generation:** PDF/CSV/HTML report generation with email delivery to multiple recipients
- **Executive Summary Reports:** Comprehensive institutional reports combining overview, trends, supervisors, retention, top risks, and recommendations
- **60+ Language Strings:** New BI terminology, report types, frequencies, analytics labels in French and English

### Technical Improvements
- BI analytics engine with SQL optimization for large datasets
- Report scheduler with cron-based execution
- Multi-format report generation (PDF via TCPDF, CSV, HTML)
- Email delivery system with file attachments
- Chart.js dual-axis visualization support
- Cohort grouping with dynamic SQL queries
- Retention calculation algorithms
- Success rate trending with historical comparison
- Next run time calculation for various frequencies

## [1.8.0] - 2025-11-17

### Added
- **Student Self-Service Dashboard:**
  - Personalized dashboard for students (student_dashboard.php)
  - Risk level display with color-coded badges
  - Gamification stats integration (points, level, streak)
  - AI-powered personalized recommendations section
  - Quick actions panel (leaderboard, calendar, courses)
  - Recent achievements showcase
  - Progress overview with interactive Chart.js visualizations
  - Daily tips and motivational messages
- **AI-Powered Recommendation Engine:**
  - Intelligent recommendation generation based on multi-factor analysis
  - 7 recommendation types:
    - Study habit improvements
    - Assignment deadlines and priorities
    - Course activity suggestions
    - Peer learning opportunities
    - Time management strategies
    - Engagement optimization
    - Resource exploration
  - Priority-based ranking (critical, high, medium, low)
  - Impact score calculation (0-100% effectiveness)
  - Actionable recommendations with direct navigation links
  - Context-aware suggestions analyzing:
    - Login frequency and temporal patterns
    - Assignment completion rates and timing
    - Course engagement levels
    - Forum participation metrics
    - Grade performance trends
    - Peak activity hour analysis
- **Anonymous Peer Comparison:**
  - Performance comparison with course peers (peer_comparison.php)
  - 4 key metrics with percentile calculations:
    - Login frequency (monthly tracking)
    - Assignment completion rate (%)
    - Engagement score (weekly activities)
    - Grade performance (average)
  - Performance categories: top (90+), above average (75-89), average (50-74), below average (25-49), needs improvement (<25)
  - Interactive radar chart visualization
  - Detailed metric comparisons with peer averages
  - Privacy-focused anonymous aggregation
  - Personalized improvement suggestions per metric
  - Peer count display for context
- **Student Progress Tracking & Goals:**
  - Personal goal creation and management (my_goals.php)
  - 5 goal types: grade, attendance, assignment, engagement, custom
  - Goal progress tracking with visual progress bars
  - Automatic completion detection with gamification rewards
  - AI-suggested goals based on performance gaps
  - Goal statistics dashboard (total, active, completed, completion rate)
  - Deadline tracking with color-coded warnings
  - Goal creation form with deadline picker
  - Completion bonus points for early achievement
- **Progress History & Analytics:**
  - Daily progress snapshots with comprehensive metrics
  - 30-day historical data tracking
  - Trend analysis algorithms (improving, declining, stable)
  - Linear regression-based slope calculations
  - Metrics tracked:
    - Risk level progression
    - Points and level advancement
    - Grade average trends
    - Weekly login frequency
    - Streak maintenance
    - Missing assignments count
  - Period comparison analytics (first half vs second half)
  - Improvement percentage calculations
  - Average completion time statistics
- **Interactive Data Visualizations:**
  - Weekly activity bar chart (7-day view)
  - Performance trend line chart (30-day)
  - Peer comparison radar chart (4 metrics)
  - Goal progress bars with percentages
  - Achievement cards with badges
  - KPI cards with real-time data updates
  - Responsive Chart.js implementations
- **Internationalization:**
  - 120+ new language strings (French/English)
  - AI recommendation messages with context
  - Peer comparison terminology
  - Goal management vocabulary
  - Progress tracking labels
  - Student tips and motivational content
  - Risk level explanations
  - Performance category descriptions

### Technical Improvements
- AI recommendation engine with multi-factor scoring algorithm
- Peer comparison manager with optimized SQL queries
- Progress tracker with snapshot system
- Linear regression implementation for trend analysis
- Percentile calculation with standard deviation
- Goal lifecycle state management
- Achievement auto-detection for goal completion
- Performance data aggregation pipelines
- Anonymous comparison data protection mechanisms
- Chart.js integration for student dashboard

### User Experience Enhancements
- Student-facing self-service interfaces
- Privacy-respecting anonymous comparisons
- Actionable insights with clear next steps
- Motivational elements and gamification
- Mobile-responsive design
- Intuitive navigation and quick actions
- Visual progress indicators
- Personalized content based on user data

## [1.7.0] - 2025-11-17

### Added
- **Email Campaign Management:**
  - Create and manage email campaigns with customizable content
  - Target audience selection (all students, at-risk, by risk level)
  - Schedule campaigns for future sending
  - Campaign status tracking (draft, scheduled, sending, sent)
  - Campaign statistics dashboard with detailed metrics
  - A/B testing system for email optimization
  - Automatic recipient splitting for A/B tests
  - Performance tracking (open rate, click rate, conversion rate)
  - Variant comparison and winner determination
  - Conversion funnel visualization
  - Campaign recipient breakdown with individual tracking
  - CSV export of campaign results
- **Student Engagement Gamification:**
  - Points system for student activities
  - Level progression with increasing thresholds
  - Daily streak tracking with bonus rewards
  - 8 predefined achievements:
    - First login (10 points)
    - Week streak (50 points)
    - Month streak (200 points)
    - All assignments completed (300 points)
    - Early submitter (100 points)
    - Helper (150 points)
    - Improvement (200 points)
    - Risk recovery (250 points)
  - Automatic achievement detection and awarding
  - Public leaderboard with rankings
  - Student gamification statistics
  - Progress tracking to next level
  - Period-based filtering (all time, month, week)
- **Mobile API Integration:**
  - RESTful API endpoints for mobile apps
  - Get student statistics (risk level, inactivity, assignments)
  - Get gamification data (points, level, achievements, streak)
  - Get leaderboard rankings
  - Get campaign statistics (admin only)
  - Web service definitions for Moodle Mobile
  - JSON-based data exchange
  - Capability-based access control
- **Campaign Analytics:**
  - Detailed campaign statistics page
  - Overall metrics (sent, opened, clicked, converted)
  - A/B test comparison charts
  - Winner determination with performance difference
  - Individual recipient tracking
  - Time-based analytics
  - Interactive Chart.js visualizations
- **Enhanced User Experience:**
  - Leaderboard page with medals for top 3
  - User stats card with progress bars
  - Achievement notifications
  - Recent achievements feed
  - Period selector for leaderboard filtering
  - Responsive design for mobile devices
  - Real-time campaign tracking
- **Internationalization:**
  - 130 new language strings in French and English
  - Campaign-related terminology
  - Gamification vocabulary
  - Achievement names and descriptions
  - API endpoint labels
  - Statistics and metrics labels

### Technical Improvements
- Email campaign manager class with A/B testing logic
- Gamification manager with achievement system
- 4 new external API functions for mobile integration
- Campaign charts JavaScript module (Chart.js)
- Database schema for campaigns, recipients, and achievements
- Service definitions for web services
- Enhanced reporting capabilities

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

## [1.6.0] - 2025-11-17

### Added
- **Predictive Analytics Engine (classes/manager/predictive_analytics.php)**
  - AI-powered risk prediction using linear regression on historical data
  - predict_risk() - Predict future risk level for individual students (3, 7, 14, or 30 days ahead)
  - Trend analysis: inactivity trend, assignment trend, engagement trend
  - Confidence scoring based on data quality and quantity (0-100%)
  - Probability distribution for each risk level
  - Key contributing factors identification
  - get_early_warnings() - Identify students predicted to become at-risk
  - generate_prediction_report() - Comprehensive prediction analytics
  - Risk transition tracking (improving, stable, deteriorating)
- **Predictions Dashboard (predictions.php)**
  - Interactive predictive analytics interface
  - 4 KPI cards: Total predictions, Early warnings, Average confidence, Deteriorating trend
  - Configurable prediction horizon (3, 7, 14, 30 days)
  - Early warnings table with detailed predictions
  - Predicted risk distribution chart (Chart.js donut)
  - Trend direction chart (Chart.js bar)
  - Key factors display for each prediction
  - Confidence filtering slider
- **Parent/Guardian Notification System (classes/manager/parent_guardian_manager.php)**
  - Complete parent/guardian management
  - register_parent() - Register parents with email, phone, relationship
  - notify_parents_critical() - Automatic critical risk notifications to parents
  - send_weekly_digest() - Weekly activity summaries for parents
  - Notification frequency settings (critical, weekly, monthly)
  - Multi-channel support (email, SMS)
  - Personalized recommendations for parents
  - Recently notified tracking (7-day cooldown)
  - Parent notification statistics
- **Parent Management Interface (parent_management.php)**
  - Parent/guardian registration form
  - 3 KPI cards: Registered parents, Notifications this month, Unique parents notified
  - Students with registered parents list
  - Quick notify parents action
  - Relationship types (parent, guardian, academic tutor)
- **Custom Report Builder (classes/manager/custom_report_builder.php)**
  - Flexible custom report creation
  - 13 available columns: student name, email, risk level, inactivity, missing assignments, notifications, last login, supervisor, interventions, grades, courses, predicted risk
  - 6 filter types: risk level, inactivity range, missing assignments range, assigned status, date range, supervisor
  - save_report_template() - Save report configurations
  - generate_report() - Execute custom SQL queries
  - Dynamic SQL query building based on columns and filters
  - export_to_csv() - CSV export with UTF-8 BOM
  - get_report_statistics() - Summary statistics for reports
  - Report template management (save, load, delete)
- **Predictions JavaScript Module (amd/src/predictions.js)**
  - Predicted risk distribution donut chart
  - Trend direction bar chart
  - High-risk prediction highlighting
  - Table sorting functionality
  - Confidence filter slider
  - Real-time row filtering

### Technical Improvements
- Linear regression algorithm for trend analysis
- Slope calculation for historical data patterns
- Confidence scoring algorithm
- Dynamic SQL query builder with parameterized queries
- Multi-column report generation
- Parent notification cooldown system
- Prediction caching and performance optimization
- Comprehensive data validation

### Files Added (6)
1. classes/manager/predictive_analytics.php - Predictive analytics engine (560 lines)
2. classes/manager/parent_guardian_manager.php - Parent/guardian management (485 lines)
3. classes/manager/custom_report_builder.php - Custom report builder (425 lines)
4. predictions.php - Predictions dashboard (235 lines)
5. parent_management.php - Parent management page (215 lines)
6. amd/src/predictions.js - Predictions charts module (180 lines)

### Files Modified
- version.php (updated to v1.6.0, version 2025111706)
- lang/en/local_student_monitor.php (+85 strings)
- lang/fr/local_student_monitor.php (+85 strings)
- CHANGELOG.md (added Phase 7 documentation)

---

## [Unreleased]

### Ideas for Future Releases
- Chatbot integration for student support
- SMS two-way communication
- WhatsApp chatbot for FAQs
- Integration with video conferencing platforms
- Integration with student information systems

---

## Version History

- **v3.0.6** (2026-02-11) - Fix `template_type` property references in template editor
- **v3.0.5** (2026-02-11) - Add 11 missing database tables and lang strings
- **v3.0.4** (2026-02-09) - Fix `fullname` usage in bulk actions and PDF manager
- **v3.0.3** (2026-02-09) - Fix remaining `fullname()` issues, localize weekly report and CSV exports
- **v3.0.2** (2026-02-07) - Fix `fullname()` "missing name fields" warnings across the plugin
- **v3.0.1** (2026-02-07) - Fix duplicate userid errors on the Predictions page
- **v3.0.0** (2026-02-07) - Simplified risk calculation (LOW/MEDIUM/HIGH/CRITICAL), tracks all course activities, removes BI module
- **v2.1.0** (2025-11-19) - Automatic alerts configuration & targeted recipient selection
- **v2.0.0** (2025-11-19) - Dedicated Students at Risk page
- **v1.9.0** (2025-11-17) - Business Intelligence dashboard & report scheduler (removed in v3.0.0)
- **v1.8.0** (2025-11-17) - Student self-service dashboard, AI recommendations, peer comparison, goals
- **v1.7.0** (2025-11-17) - Email campaigns, gamification, mobile API
- **v1.6.0** (2025-11-17) - Predictive Analytics & Parent Notifications
- **v1.5.0** (2025-11-17) - Workflow Automation & Task Management
- **v1.4.0** (2025-11-17) - PDF Export & Communication Management
- **v1.3.0** (2025-11-17) - Visualization & Advanced Reporting
- **v1.2.0** (2025-11-17) - Configuration & Testing
- **v1.1.0** (2025-11-17) - UI, Dashboard & Manual Alerts
- **v1.0.0** (2025-11-17) - Initial release

---

## Contributors

- **kriimoohh** - Auteur principal
- **UNCHK (Université Numérique Cheikh Hamidou KANE)** - Institution d'origine, Sénégal

> Plugin développé pour l'UNCHK mais utilisable par toute institution éducative.

---

## License

GNU GPL v3 or later

---

For more information, see README.md
