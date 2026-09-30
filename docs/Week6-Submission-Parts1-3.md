# IT PROF EL 4 – Advanced System Integration and Architecture
# Semester Project, Week 6 Documentation Submission
## PLANNING, SECURITY, AND YOUR FIRST REPOSITORY ENTRY

**Submission Form**

| Field | Entry |
|---|---|
| Partner 1 | Hermie Jay Balaba |
| Partner 2 | Marc Erfred Mabuti |
| Course/Section | BS-IT / 80107 |
| Date | 09/02/2026 |
| Chosen App or Organization (semester project) | **VIBER APP** |

---

## Part 1. Planning Question Evaluation

**New request being evaluated (one sentence):** The organization's officers want to adopt Viber as the official channel for sending event announcements and reminders to all members.

**1. Does this solve a real problem?**
Yes — announcements are currently scattered across Facebook posts, text messages, and word-of-mouth, so many members miss important updates like schedule changes or venue shifts.

**2. Do we already have something that can do this?**
Partially — the org has a Facebook Group, but not all members check it regularly, and Facebook notifications are often missed or buried, so it isn't reliably solving the problem.

**3. Does this support the organization's actual goals?**
Yes — the org's goal this semester includes improving member engagement and reducing missed announcements, and Viber's broadcast and community features directly support that.

**4. Is it secure and cost-effective?**
Yes — Viber is free to use, requires no special hardware beyond a phone, and offers admin-controlled community/group settings so only authorized officers can post official announcements.

**Where would this rank against other approved work?** (value, risk, dependency, or effort, reasoned in words, not calculated)

High priority, low effort. Value is high since it affects communication for every member and every event, risk is low because no sensitive financial or academic data is shared through it, there's no dependency on other systems, and effort is minimal — just creating the community and inviting members, no technical setup required.

---

## Part 2. Security Pass Across the Four Domains

| Domain | Security Concern | Matching Control |
|---|---|---|
| Business | Important announcements could be missed or misread if too many members post unrelated messages, burying official updates | Restrict posting rights to officers only in the "Community" (broadcast-style) settings |
| Data | Members' phone numbers become visible or linkable within group settings, risking unwanted contact or spam | Use Viber's "Community" feature instead of a regular group, which hides member phone numbers from each other |
| Application | An officer's account could be used to post fake or misleading announcements if compromised | Limit the number of officers with admin/posting rights, and require them to log out of shared devices |
| Technology | Members using outdated versions of the Viber app may miss security patches, exposing them to vulnerabilities | Encourage members to keep the app updated via reminders in onboarding instructions |

**One weak-link risk** (how a weak point in one part could put a stronger part at risk): If an officer's Viber account is compromised, an attacker could post fake or misleading announcements, undermining the security controls and reliability of the organization's official communication channel.

---

## Part 3. Your First Repository Entry

**Component being documented:** Viber Community (Broadcast Communication Channel)

| Field | Your Entry |
|---|---|
| What it does | Provides a community/broadcast channel for officers to send official announcements, reminders, and updates directly to all members' phones |
| Which department or user group uses it | Organization officers (senders) and all general members (recipients) |
| What it connects to | Members' personal phone numbers (via Viber account); no direct integration with other org systems, functions as a standalone communication channel |
| Who is responsible for it | Designated communications officer (posting and moderation); other officers with admin rights as backup |
| Whether it is still needed, and why | Yes — needed as long as the org relies on mobile-based communication for reaching members quickly; should be reviewed only if the org shifts to a unified platform (e.g., a dedicated app or LMS-integrated messaging) that consolidates announcements and other functions |
