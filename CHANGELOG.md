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

## [Unreleased]

### Planned for v1.1.0
- [ ] Dashboard UI implementation with Mustache templates
- [ ] JavaScript charts integration (Chart.js)
- [ ] Advanced filtering and search in student list
- [ ] Bulk actions for students
- [ ] Email template editor in admin interface
- [ ] SMS cost tracking
- [ ] WhatsApp template messages support
- [ ] Mobile app notifications (via Firebase)
- [ ] Predictive analytics for at-risk detection
- [ ] Integration with external learning analytics
- [ ] Automated intervention workflows
- [ ] Parent/guardian notifications
- [ ] Multi-language template support
- [ ] Advanced reporting with PDF export

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
