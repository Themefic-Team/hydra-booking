(function(e){e(document).ready(function(){e(document).on("submit",".tfhb-meeting-cencel-form",function(i){i.preventDefault(),$this=e(this);var n=new FormData(this);n.append("action","tfhb_meeting_form_cencel"),n.append("nonce",tfhb_app_booking.nonce),$this.find(".tfhb-notice").remove(),e.ajax({url:tfhb_app_booking.ajax_url,type:"POST",data:n,processData:!1,contentType:!1,success:function(t){t.success?($this.find(".tfhb-meeting-confirmation .tfhb-forms").html(""),$this.find(".tfhb-meeting-confirmation").append(`<div class="tfhb-notice tfhb-success">${t.data.message}</div>`)):$this.find(".tfhb-meeting-confirmation").append(`<div class="tfhb-notice tfhb-error">${t.data.message}</div>`)},error:function(t){console.log(t)}})})})})(jQuery);
