# Tenant Application Status View Testing Plan

## Objective
Verify that the tenant application status view correctly displays the status updates and details of tenant applications.

## Test Cases

1. **Verify Status Display**
   - Check if the status (Pending, Approved, Rejected) is correctly shown for each application.
   - Ensure the color coding for statuses matches the design (e.g., Green for Approved, Red for Rejected).

2. **Verify Application Details**
   - Click on an application to view details.
   - Confirm that tenant name, property applied for, and submission date are accurate.
   - Check if uploaded documents are accessible.

3. **Verify Status Update Reflection**
   - Change the status of an application in the backend/admin panel.
   - Refresh the view or check if it updates in real-time.
   - Confirm the new status is reflected immediately.

4. **Edge Cases**
   - Test with an application having a very long property name.
   - Test with no applications in the list (empty state).

## Approval and Rejection Workflow

1. **Approve Application**
   - Use the `update_application_status.php` endpoint (or UI button) to set status to 'Approved'.
   - Verify database is updated.
   - Verify UI reflects 'Approved'.

2. **Reject Application**
   - Use the `update_application_status.php` endpoint (or UI button) to set status to 'Rejected'.
   - Verify database is updated.
   - Verify UI reflects 'Rejected'.

3. **Permission Check**
   - Attempt to update status as a non-owner/non-admin.
   - Verify access is denied (403 Forbidden).
