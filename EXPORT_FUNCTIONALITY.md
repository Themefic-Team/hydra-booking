# Calendar Export Functionality

This document describes the export functionality implemented for the Hydra Booking calendar in the sellers' appointments view.

## Overview

The export functionality allows sellers to export their calendar events in two formats:
1. **iCal (.ics)** - For importing into calendar applications
2. **PDF** - For printing or sharing as a document

## Implementation Details

### Files Modified

- `src/view/FrontendDashboard/sellers/Appointments.vue` - Main implementation
- `package.json` - Added jsPDF and html2canvas dependencies
- `test-export.html` - Test file for functionality verification

### Dependencies Added

```json
{
  "jspdf": "^2.5.1",
  "html2canvas": "^1.4.1"
}
```

## Export Functions

### 1. iCal Export (`exportAsICal`)

**Features:**
- Generates standard iCal format (.ics file)
- Includes all event details: title, date, time, status, description, location
- Properly formatted dates for calendar applications
- Unique UID for each event
- Automatic file download with timestamp

**iCal Format:**
```ical
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Hydra Booking//Calendar Export//EN
CALSCALE:GREGORIAN
METHOD:PUBLISH
BEGIN:VEVENT
UID:event-id@hydra-booking
DTSTAMP:20250115T100000Z
DTSTART:20250115T100000Z
DTEND:20250115T110000Z
SUMMARY:Event Title
STATUS:CONFIRMED
DESCRIPTION:Event description
LOCATION:Event location
END:VEVENT
END:VCALENDAR
```

### 2. PDF Export (`exportAsPDF`)

**Features:**
- Professional PDF layout with proper styling
- Event details including: title, date, time, status, contact info, company, address, description
- Color-coded status indicators
- Multi-page support for large calendars
- Fallback to print method if PDF generation fails
- High-quality rendering using html2canvas

**PDF Content Structure:**
- Header with export date and total events count
- Individual event cards with all details
- Status color coding (confirmed=green, pending=orange, canceled=red)
- Responsive layout optimized for A4 pages

## Usage

### Button Interface

The export functionality is accessible through two buttons in the calendar controls:

```html
<button class="tfhb-btn secondary-btn" @click="exportCalendar('iCal')">
    <Icon name="FileDown" size=16 />
    Export as .iCal
</button>
<button class="tfhb-btn secondary-btn" @click="exportCalendar('PDF')">
    <Icon name="FileText" size=16 />
    Export Calendar PDF
</button>
```

### Function Calls

```javascript
// Export as iCal
exportCalendar('iCal');

// Export as PDF
exportCalendar('PDF');
```

## Error Handling

### iCal Export
- Validates that events exist before export
- Shows error toast if no events are available
- Handles missing data gracefully

### PDF Export
- Primary method: Uses jsPDF + html2canvas for high-quality PDFs
- Fallback method: Uses browser print dialog if PDF generation fails
- Comprehensive error handling with user feedback
- Automatic cleanup of temporary DOM elements

## Data Sources

The export functions use data from:
- `calendarEvents.value` - Array of calendar events
- Event structure includes:
  - `id` - Unique event identifier
  - `title` - Event title
  - `start` - Start date/time
  - `end` - End date/time
  - `extendedProps.status` - Event status
  - `extendedProps.apiData` - Full API data with buyer/seller information

## Helper Functions

### Data Extraction Functions
- `getBuyerName(apiData)` - Extracts buyer name from API data
- `getCompanyWebsite(apiData)` - Extracts company website
- `getAddress(apiData)` - Extracts address information
- `getAreasOfActivity(apiData)` - Extracts areas of activity
- `getPreferredMeetings(apiData)` - Extracts preferred meeting types

### Date Formatting
- Proper iCal date format (YYYYMMDDTHHMMSSZ)
- Human-readable date/time formatting for PDF
- Timezone handling for calendar applications

## File Naming

### iCal Files
- Format: `calendar-export-YYYY-MM-DD.ics`
- Example: `calendar-export-2025-01-15.ics`

### PDF Files
- Format: `calendar-export-YYYY-MM-DD.pdf`
- Example: `calendar-export-2025-01-15.pdf`

## Browser Compatibility

### iCal Export
- Works in all modern browsers
- Uses standard Blob API for file download
- No external dependencies required

### PDF Export
- Primary: Modern browsers with jsPDF support
- Fallback: Any browser with print functionality
- Requires jsPDF and html2canvas libraries

## Testing

A test file (`test-export.html`) is provided to verify the export functionality:
- Tests both iCal and PDF export with sample data
- Includes error handling demonstrations
- Can be opened in any browser for testing

## Future Enhancements

Potential improvements:
1. **CSV Export** - For spreadsheet applications
2. **Email Integration** - Direct email sharing
3. **Cloud Storage** - Save to Google Drive, Dropbox, etc.
4. **Custom Templates** - Different PDF layouts
5. **Filtered Exports** - Export only specific date ranges or statuses
6. **Batch Processing** - Export multiple calendars at once

## Troubleshooting

### Common Issues

1. **PDF Generation Fails**
   - Check browser console for errors
   - Verify jsPDF and html2canvas are loaded
   - Try the fallback print method

2. **iCal Import Issues**
   - Verify the .ics file format is correct
   - Check that dates are properly formatted
   - Ensure UIDs are unique

3. **Large Calendar Performance**
   - Consider pagination for very large calendars
   - Implement loading indicators for long operations
   - Add progress bars for large exports

### Debug Information

Enable console logging for debugging:
```javascript
console.log('Calendar events:', calendarEvents.value);
console.log('Export format:', format);
```

## Security Considerations

- No sensitive data is exposed in exports
- File downloads use standard browser APIs
- No server-side processing required
- Client-side only implementation

## Performance Notes

- iCal export is very fast (client-side only)
- PDF export may take longer for large calendars
- Memory usage scales with calendar size
- Consider implementing pagination for very large exports 