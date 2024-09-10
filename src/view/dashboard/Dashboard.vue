<script setup>

import Chart from 'primevue/chart';
import { ref, onMounted  } from 'vue';
import Header from '@/components/Header.vue';
import Icon from '@/components/icon/LucideIcon.vue'
import HbDateTime from '@/components/form-fields/HbDateTime.vue';


// Store 
import { Dashboard } from '@/store/dashboard';
import { Notification } from '@/store/notification';
 
const datachart_box_dropdown = ref(false);
const datachart_dropdown = ref(false);

onMounted(() => {
    Dashboard.fetcDashboard(); 
    Dashboard.fetcDashboardStatistics();
    Notification.fetchNotifications();
}); 

const updateDashboardDay = (day) => { 
    Dashboard.skeleton_chartbox = true;

    // selected attribute data-name
    const dropdown = document.getElementById('tfhb-datachart-filter');
    dropdown.querySelector('span').innerText = event.target.getAttribute('data-name');
    
    Dashboard.data_request.days = day;
    Dashboard.fetcDashboard();
    datachart_box_dropdown.value = false;
}

const updateDashboardDateRange = () => { 
    Dashboard.skeleton_chartbox = true;
    Dashboard.fetcDashboard();
    datachart_box_dropdown.value = false;
}

const  ChangeStatisticData = (day) => { 
    Dashboard.data_request.statistics_days = day;  
    Dashboard.skeleton_chart = true;
    datachart_dropdown.value = false;
    // selected attribute data-name
    const dropdown = document.getElementById('tfhb-chart-filter');
    dropdown.querySelector('span').innerText = event.target.getAttribute('data-name');
    
    Dashboard.fetcDashboardStatistics(); 
}


</script>
<template>

<!-- {{ tfhbClass }} -->
<div class="tfhb-admin-dashboard tfhb-admin-meetings "> 
    <Header title="Dashboard" :notifications="Notification.Data" /> 
    <div  :class="{ 'tfhb-skeleton': Dashboard.skeleton }"  class="tfhb-dashboard-heading tfhb-flexbox">
        <div class="thb-admin-title">
            <h2>{{ $tfhb_trans('Data') }}</h2>
            <p>{{ $tfhb_trans('One-liner description') }}</p> 
        </div>  
        <div class="tfhb-dropdown tfhb-mega-dropdown tfhb-no-hover">
            <span class="tfhb-flexbox tfhb-gap-8 tfhb-mega-dropdown-heading " @click="datachart_box_dropdown = !datachart_box_dropdown"  id="tfhb-datachart-filter"> <span>{{ $tfhb_trans('Today') }}</span>  <Icon name="ChevronDown" size="20" /> </span>
            <div 
                :class="{ 'active': datachart_box_dropdown }"
                class="tfhb-dropdown-wrap"
            > 
                <!-- route link -->
                <span @click="updateDashboardDay(1)" data-name="Today" class="tfhb-dropdown-single">{{ $tfhb_trans('Today') }}</span>
                <span  @click="updateDashboardDay(7)" data-name="Last 7 week" class="tfhb-dropdown-single">{{ $tfhb_trans('Last 7 week') }}</span> 
                <span  @click="updateDashboardDay(30)" data-name="Last 30 Days" class="tfhb-dropdown-single">{{ $tfhb_trans('Last 30 Days') }}</span> 
                <span  @click="updateDashboardDay(60)" data-name="Last 3 months" class="tfhb-dropdown-single">{{ $tfhb_trans('Last 3 months') }}</span> 
                <div class="tfhb-dropdown-single">
                    <div class="tfhb-filter-dates tfhb-flexbox tfhb-gap-8">
                        <div class="tfhb-filter-start-date">
                            <!-- <span>{{ $tfhb_trans('From') }}</span> -->
                            <HbDateTime 
                                v-model="Dashboard.data_request.from_date"
                                selected = "1" 
                                label=""
                                enableTime='true'
                                placeholder="From"  
                                icon="CalendarDays"   
                            />  
                        </div>
                        <div class="tfhb-calender-move-icon">
                            <Icon name="MoveRight" size="15" /> 
                        </div>
                        <div class="tfhb-filter-end-date">
                            <!-- <span>{{ $tfhb_trans('To') }}</span> -->
                            <HbDateTime 
                                v-model="Dashboard.data_request.to_date"
                                selected = "1" 
                                enableTime='true'
                                placeholder="To"   
                                icon="CalendarDays"   
                            />  
                        </div>
                    </div>

                    <button class="tfhb-btn tfhb-btn-primary boxed-btn tfhb-mt-16 tfhb-full-width" @click="updateDashboardDateRange">{{ $tfhb_trans('Apply') }}</button>
                </div> 
            </div>
        </div>
    </div>
    <div  :class="{ 'tfhb-skeleton': Dashboard.skeleton }"  class="tfhb-dashboard-wrap">
   
        <div :class="{ 'tfhb-skeleton': Dashboard.skeleton_chartbox }"  class="tfhb-dashboard-chartbox tfhb-flexbox tfhb-gap-24">

            <!-- Single Chartbox -->
            <div class="tfhb-single-chartbox">
                <div class="tfhb-single-chartbox-wrap gradient-1">
                    <span class="tfhb-adminchartbox-shape">
                        <!-- <img  :src="$tfhb_url+'/assets/images/shape-1.svg'" alt=""> -->
                    </span>

                    <span class="cartbox-title">{{ $tfhb_trans('Total Booking') }}</span> 
                    <div class="tfhb-single-cartbox-inner tfhb-flexbox tfhb-gap-8">
                        <div class="tfhb-single-chartbox-content">
                            <span class="cartbox-value ">{{Dashboard.data.total_bookings.total}}</span>
                            
                        </div>
                        <div class="tfhb-chartbox-icon">
                            <img  :src="$tfhb_url+'/assets/images/total-booking.svg'" alt="">

                        </div>
                    </div>
                    
                    <div class="cartbox-meta tfhb-flexbox tfhb-gap-8">
                        <span class="cartbox-badge tfhb-flexbox tfhb-gap-8"
                            :class = "{
                                'badge-down': Dashboard.data.total_bookings.growth == 'decrease',
                                'badge-up': Dashboard.data.total_bookings.growth == 'increase',
                            }"
                        >
                            <Icon v-if="Dashboard.data.total_bookings.growth == 'increase'" name="ArrowUp" :size="15"/>
                            <Icon v-else name="ArrowDown" :size="15"/>
                            <span> {{Dashboard.data.total_bookings.percentage}}%</span>
                        </span>
                        <span> {{ $tfhb_trans('VS') }} </span>
                        <span class="cartbox-date">{{ $tfhb_trans('Last') }} {{Dashboard.data_request.days}} {{ $tfhb_trans('days') }}</span>
                    </div>
                </div>
            </div>
            <!-- Single Chartbox --> 
            <!-- Single Chartbox -->
            <div class="tfhb-single-chartbox">
                <div class="tfhb-single-chartbox-wrap gradient-2">
                    <span class="tfhb-adminchartbox-shape">
                        <!-- <img  :src="$tfhb_url+'/assets/images/shape-2.svg'" alt=""> -->
                    </span>

                    <span class="cartbox-title">{{ $tfhb_trans('Total Earnings') }}</span> 
                    <div class="tfhb-single-cartbox-inner tfhb-flexbox tfhb-gap-8">
                        <div class="tfhb-single-chartbox-content">
                            <span class="cartbox-value ">0</span>
                            
                        </div>
                        <div class="tfhb-chartbox-icon">
                            <img  :src="$tfhb_url+'/assets/images/total-earning.svg'" alt="">

                        </div>
                    </div>
                    
                    <div class="cartbox-meta tfhb-flexbox tfhb-gap-8">
                        <span class="cartbox-badge badge-down tfhb-flexbox tfhb-gap-8">
                            <Icon name="ArrowUp" :size="15"/>
                            <span> 80%</span>
                        </span>
                        <span> {{ $tfhb_trans('VS') }} </span>
                        <span class="cartbox-date">{{ $tfhb_trans('Last') }} 30 {{ $tfhb_trans('days') }}</span>
                    </div>
                </div>
            </div>
            <!-- Single Chartbox --> 

            <!-- Single Chartbox -->
            <div class="tfhb-single-chartbox">
                <div class="tfhb-single-chartbox-wrap gradient-3">
                    <span class="tfhb-adminchartbox-shape">
                        <!-- <img  :src="$tfhb_url+'/assets/images/shape-3.svg'" alt=""> -->
                    </span>

                    <span class="cartbox-title">{{ $tfhb_trans('Completed Bookings') }}</span> 
                    <div class="tfhb-single-cartbox-inner tfhb-flexbox tfhb-gap-8">
                        <div class="tfhb-single-chartbox-content">
                            <span class="cartbox-value ">{{Dashboard.data.total_completed_bookings.total}}</span>
                            
                        </div>
                        <div class="tfhb-chartbox-icon">
                            <img  :src="$tfhb_url+'/assets/images/complete-booking.svg'" alt="">

                        </div>
                    </div>
                    
                    <div class="cartbox-meta tfhb-flexbox tfhb-gap-8">
                        <span class="cartbox-badge tfhb-flexbox tfhb-gap-8"
                            :class = "{
                                'badge-down': Dashboard.data.total_completed_bookings.growth == 'decrease',
                                'badge-up': Dashboard.data.total_completed_bookings.growth == 'increase',
                            }"
                        >
                            <Icon v-if="Dashboard.data.total_completed_bookings.growth == 'increase'" name="ArrowUp" :size="15"/>
                            <Icon v-else name="ArrowDown" :size="15"/>
                            <span> {{Dashboard.data.total_completed_bookings.percentage}}%</span>
                        </span>
                        <span> {{ $tfhb_trans('VS') }} </span>
                        <span class="cartbox-date">{{ $tfhb_trans('Last') }} {{Dashboard.data_request.days}} {{ $tfhb_trans('days') }}</span>
                    </div>
                </div>
            </div>
            <!-- Single Chartbox --> 
            <!-- Single Chartbox -->
            <div class="tfhb-single-chartbox">
                <div class="tfhb-single-chartbox-wrap gradient-4">
                    <span class="tfhb-adminchartbox-shape">
                        <!-- <img  :src="$tfhb_url+'/assets/images/shape-4.svg'" alt=""> -->
                    </span>

                    <span class="cartbox-title">{{ $tfhb_trans('Canceled Bookings') }}</span> 
                    <div class="tfhb-single-cartbox-inner tfhb-flexbox tfhb-gap-8">
                        <div class="tfhb-single-chartbox-content">
                            <span class="cartbox-value ">{{Dashboard.data.total_cancelled_bookings.total}}</span>
                            
                        </div>
                        <div class="tfhb-chartbox-icon">
                            <img  :src="$tfhb_url+'/assets/images/cancel-booking.svg'" alt="">

                        </div>
                    </div>
                    
                    <div class="cartbox-meta tfhb-flexbox tfhb-gap-8">
                        <span class="cartbox-badge tfhb-flexbox tfhb-gap-8"
                            :class = "{
                                'badge-down': Dashboard.data.total_cancelled_bookings.growth == 'decrease',
                                'badge-up': Dashboard.data.total_cancelled_bookings.growth == 'increase',
                            }"
                        >
                            <Icon v-if="Dashboard.data.total_cancelled_bookings.growth == 'increase'" name="ArrowUp" :size="15"/>
                            <Icon v-else name="ArrowDown" :size="15"/>
                            <span> {{Dashboard.data.total_cancelled_bookings.percentage}}%</span>
                        </span>
                        <span> {{ $tfhb_trans('VS') }} </span>
                        <span class="cartbox-date">{{ $tfhb_trans('Last') }} {{Dashboard.data_request.days}} {{ $tfhb_trans('days') }}</span>
                    </div>
                </div>
            </div>
            <!-- Single Chartbox --> 

                
        </div>

        
        <!-- Notice Box -->
        <div :class="{ 'tfhb-skeleton': Dashboard.skeleton_chartbox }"  class="tfhb-flexbox tfhb-dashboard-notice-box tfhb-gap-24">

            <div class="tfhb-dashboard-notice-box-inner">
                <div class="tfhb-dashboard-notice-box-wrap tfhb-flexbox tfhb-gap-16">

                    <h3 class="tfhb-dashboard-notice-box-title tfhb-m-0 tfhb-full-width">{{ $tfhb_trans('Recent Bookings') }}</h3>
                    <!-- Single Notice Box -->
                    <div class="tfhb-dashboard-notice-box-content tfhb-flexbox tfhb-gap-16 tfhb-full-width">
                        <div
                            v-for="(data, index) in Dashboard.data.recent_booking"
                                :key="index" 
                            class="tfhb-dashboard-notice-single-box tfhb-full-width" 
                        >
                            <div class="tfhb-admin-card-box">
                                
                                <p>{{data.title}}    </p>
                                <div class="tfhb-dashboard-notice-meta tfhb-flexbox tfhb-gap-8"> 
                                    <span class="tfhb-flexbox tfhb-gap-8"><Icon name="Clock" :size="15"/>{{ data.start_time}} </span>
                                    <span  class="tfhb-flexbox tfhb-gap-8">
                                        <Icon name="UserRound" :size="15"/> 
                                        <Icon name="ArrowRight" :size="15"/> 
                                        <Icon name="UserRound" :size="15"/> 
                                        <Icon v-if="data.meeting_type != 'one-to-one'" name="UserRound" :size="15"/> 
                                    </span>

                                    <span  class="tfhb-flexbox tfhb-gap-8"><Icon name="Banknote" :size="15"/> {{data.payment_status}} </span>
                                    <span  class="tfhb-flexbox tfhb-gap-8"><Icon name="UserRound" :size="15"/> {{data.host_first_name}} {{ data.host_last_name}} </span>
                                </div>
                            </div> 
                        </div>
                    </div>
                    <!-- Single Notice Box -->
                    
                </div>
            </div>

            <div class="tfhb-dashboard-notice-box-inner">
                <div class="tfhb-dashboard-notice-box-wrap ">
                    <h3 class="tfhb-dashboard-notice-box-title tfhb-mb-24 tfhb-full-width">{{ $tfhb_trans('Upcoming Meetings') }}</h3>

                    <div class="tfhb-dashboard-notice-box-content tfhb-flexbox tfhb-gap-16" >
                        <!-- Single Notice Box -->
                        <div 
                            v-for="(data, index) in Dashboard.data.upcoming_booking"
                            :key="index" 
                            class="tfhb-dashboard-notice-single-box tfhb-flexbox tfhb-gap-8 tfhb-full-width" >
                            <span > {{ data.start_time}} </span>
                            <div class="tfhb-admin-card-box">
                                <p>{{data.attendee_name}} ({{data.attendee_email}})  </p>
                                <div class="tfhb-dashboard-notice-meta tfhb-flexbox tfhb-gap-8"> 
                                    <span class="tfhb-flexbox tfhb-gap-8"><Icon name="CalendarDays" :size="15"/> 
                                        <!-- convert 2024-05-29 to 25 Sep, 24 --> 
                                        {{data.meeting_dates}}
                                    </span> 
                                    <span  class="tfhb-flexbox tfhb-gap-8"><Icon name="Clock" :size="15"/> {{ data.attendee_time_zone}}</span>
                                    <span  class="tfhb-flexbox tfhb-gap-8"><Icon name="UserRound" :size="15"/> {{data.host_first_name}} {{ data.host_last_name}}</span>
                                </div>
                            </div> 
                        </div> 
                    </div> 

                </div>
            </div>



        </div>

        <!-- Cart statistic -->
        <div   class="tfhb-chart-statistic-wrap tfhb-dashboard-notice-box"> 
            <div class="tfhb-dashboard-notice-box-wrap" >
                <div  class="tfhb-dashboard-heading tfhb-flexbox">
                    <div class="tfhb-admin-title"> 
                        <h3 >{{ $tfhb_trans('Statistics') }}</h3>  
                    </div>
                    <div class="thb-admin-btn right"> 
                        <div class="tfhb-dropdown  tfhb-no-hover">
                            <a class="tfhb-flexbox tfhb-gap-8 tfhb-btn"  @click="datachart_dropdown = !datachart_dropdown" id="tfhb-chart-filter" > <span> {{ $tfhb_trans('Last 7 Days') }}</span>  <Icon name="ChevronDown" size="20" /> </a>
                            <div  
                                :class="{ 'active': datachart_dropdown }"
                                class="tfhb-dropdown-wrap"
                            > 
                                <!-- route link --> 
                                <span class="tfhb-dropdown-single" data-name="Last 7 Days" @click="ChangeStatisticData(7)">{{ $tfhb_trans('Last 7 Days') }}</span> 
                                <span class="tfhb-dropdown-single" data-name="This month" @click="ChangeStatisticData(30)">{{ $tfhb_trans('This month') }}</span> 
                                <span class="tfhb-dropdown-single" data-name="Last 3 months" @click="ChangeStatisticData(3)">{{ $tfhb_trans('Last 3 months') }}</span> 
                                <span class="tfhb-dropdown-single" data-name="This Year" @click="ChangeStatisticData(12)">{{ $tfhb_trans('This Year') }}</span> 
                                
                            </div>
                        </div> 
                    </div> 
                </div>
                
                <Chart :class="{ 'tfhb-skeleton': Dashboard.skeleton_chart }" type="line" :data="Dashboard.chartData" :options="Dashboard.chartOptions" />
    
            </div>
        </div>
    </div> 
</div> 
</template>
 