export default function useDateFormat() {

    const Tfhb_Date = (dateString) => { 
        const dates = dateString.split(','); 

        // make array of date objects
        const dateObjects = dates.map(date => {
            const options = { day: 'numeric', month: 'long', year: 'numeric' };
            const dateObject = new Date(date);
            return dateObject.toLocaleDateString('en-US', options);
        });
        // make array to string
        return dateObjects.join(', ');
        console.log('dateObjects', dateObjects);
    }

    const Tfhb_Time = (timeString) => {
        const options = { hour: '2-digit', minute: '2-digit', hour12: true };
        const time = new Date(timeString);
        return time.toLocaleTimeString('en-US', options);
    }
    
    return { Tfhb_Date, Tfhb_Time }
}