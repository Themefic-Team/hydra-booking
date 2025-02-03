export default function useDateFormat() {

    const Tfhb_Date = (dateString) => {
        // const options = { day: 'numeric', month: 'long', year: 'numeric' };
        // const date = new Date(dateString);
        // return date.toLocaleDateString('en-US', options);
 
        if (!dateString) return '';
        const dates = dateString.split(',');  
        // make array of date objects
        const dateObjects = dates.map(date => {
            const options = { day: 'numeric', month: 'long', year: 'numeric' };
            const dateObject = new Date(date);
            return dateObject.toLocaleDateString('en-US', options);
        }); 
        // make array to string
        return dateObjects.join(', '); 
    }

    const Tfhb_Time = (timeString) => {
        const options = { hour: '2-digit', minute: '2-digit', hour12: true };
        const time = new Date(timeString);
        return time.toLocaleTimeString('en-US', options);
    }

    // full date and time format
    const Tfhb_DateTime = (dateString) => {
        // 2025-01-01 14:39:25.000000 to December 19, 2024, 6:35 pm 
        const options = { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true };
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', options);

    }
    
    return { Tfhb_Date, Tfhb_Time, Tfhb_DateTime }
}