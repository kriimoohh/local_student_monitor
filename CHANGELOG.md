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

## [Unreleased]

### Planned for v1.2.0
- [ ] Course-specific settings page (course_settings.php)
- [ ] Student preferences page
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
