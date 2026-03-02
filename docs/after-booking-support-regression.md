# After Booking Support Team Regression Checks

Run these checks after deploying changes for the admin-only booking operations view rename.

1. Log in as an admin and open:
   - `/covermenow-one/?view=after-booking-support`
2. Confirm page heading shows `After Booking Support Team`.
3. Confirm sidebar entry under `Bookings` shows `After Booking Support Team`.
4. Open legacy URL directly:
   - `/covermenow-one/?view=war-room`
5. Confirm legacy URL still lands on the same screen and controls work.
6. On both URLs, test both form actions on a row:
   - `Refresh request`
   - `Mark no longer needed`
7. Confirm there are no console errors on load and during auto-refresh.
