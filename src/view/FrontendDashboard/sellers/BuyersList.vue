<script setup> 
import { ref, onBeforeMount, defineProps  } from 'vue';  
</script>

<template > 
    <div class="tfhb-admin-hosts" >
        <div class="tfhb-hosts-content">
            <div class="tfhb-hosts-list-wrap tfhb-flexbox"> 
                <!-- Single Hosts -->
                <div class="tfhb-single-hosts"> 
                    <div class="tfhb-single-hosts-wrap ">
                        <span class="tfhb-hosts-status" >{{ $tfhb_trans('Active') }}</span> 
                        <!-- <span class="tfhb-hosts-status tfhb-hosts-status-warning" v-else>{{ $tfhb_trans('Disable') }}</span> -->

                        <div class="tfhb-hosts-info tfhb-flexbox">
                            <div class="hosts-avatar" >
                                <img   :src="$tfhb_url+'/assets/images/avator.png'" alt="Hosts Avatar">
                            </div>
                            <div class="hosts-details">
                                <h3>Sellers name</h3> 
                            </div>
                        </div>
                        <hr>
                        <div class="tfhb-hosts-info tfhb-flexbox">  
                            <span class="tfhb-info-icon-text tfhb-flexbox" > <Icon name="Mail" size=20 />sellers@sellers.com</span>
                            <span class="tfhb-info-icon-text tfhb-flexbox" > <Icon name="Phone" size=20 />000000000000000</span>
                        </div>
                        <!-- <button class="tfhb-single-hosts-action"><Icon name="ListCollapse" size=20 /></button> -->
                    <div  class="tfhb-single-hosts-action tfhb-dropdown">
                        <img :src="$tfhb_url+'/assets/images/more-vertical.svg'" alt=""> 
                    </div>
                    </div> 
                </div>
                <!-- Single Hosts -->
                <!-- Single Hosts -->
                <div class="tfhb-single-hosts"> 
                    <div class="tfhb-single-hosts-wrap ">
                        <span class="tfhb-hosts-status" >{{ $tfhb_trans('Active') }}</span> 
                        <!-- <span class="tfhb-hosts-status tfhb-hosts-status-warning" v-else>{{ $tfhb_trans('Disable') }}</span> -->

                        <div class="tfhb-hosts-info tfhb-flexbox">
                            <div class="hosts-avatar" >
                                <img   :src="$tfhb_url+'/assets/images/avator.png'" alt="Hosts Avatar">
                            </div>
                            <div class="hosts-details">
                                <h3>Sellers name</h3> 
                            </div>
                        </div>
                        <hr>
                        <div class="tfhb-hosts-info tfhb-flexbox">  
                            <span class="tfhb-info-icon-text tfhb-flexbox" > <Icon name="Mail" size=20 />sellers@sellers.com</span>
                            <span class="tfhb-info-icon-text tfhb-flexbox" > <Icon name="Phone" size=20 />000000000000000</span>
                        </div>
                        <!-- <button class="tfhb-single-hosts-action"><Icon name="ListCollapse" size=20 /></button> -->
                    <div  class="tfhb-single-hosts-action tfhb-dropdown">
                        <img :src="$tfhb_url+'/assets/images/more-vertical.svg'" alt=""> 
                    </div>
                    </div> 
                </div>
                <!-- Single Hosts -->
                <!-- Single Hosts -->
                <div class="tfhb-single-hosts"> 
                    <div class="tfhb-single-hosts-wrap ">
                        <span class="tfhb-hosts-status" >{{ $tfhb_trans('Active') }}</span> 
                        <!-- <span class="tfhb-hosts-status tfhb-hosts-status-warning" v-else>{{ $tfhb_trans('Disable') }}</span> -->

                        <div class="tfhb-hosts-info tfhb-flexbox">
                            <div class="hosts-avatar" >
                                <img   :src="$tfhb_url+'/assets/images/avator.png'" alt="Hosts Avatar">
                            </div>
                            <div class="hosts-details">
                                <h3>Sellers name</h3> 
                            </div>
                        </div>
                        <hr>
                        <div class="tfhb-hosts-info tfhb-flexbox">  
                            <span class="tfhb-info-icon-text tfhb-flexbox" > <Icon name="Mail" size=20 />sellers@sellers.com</span>
                            <span class="tfhb-info-icon-text tfhb-flexbox" > <Icon name="Phone" size=20 />000000000000000</span>
                        </div>
                        <!-- <button class="tfhb-single-hosts-action"><Icon name="ListCollapse" size=20 /></button> -->
                    <div  class="tfhb-single-hosts-action tfhb-dropdown">
                        <img :src="$tfhb_url+'/assets/images/more-vertical.svg'" alt=""> 
                    </div>
                    </div> 
                </div>
                <!-- Single Hosts -->
                <!-- Single Hosts -->
                <div class="tfhb-single-hosts"> 
                    <div class="tfhb-single-hosts-wrap ">
                        <span class="tfhb-hosts-status" >{{ $tfhb_trans('Active') }}</span> 
                        <!-- <span class="tfhb-hosts-status tfhb-hosts-status-warning" v-else>{{ $tfhb_trans('Disable') }}</span> -->

                        <div class="tfhb-hosts-info tfhb-flexbox">
                            <div class="hosts-avatar" >
                                <img   :src="$tfhb_url+'/assets/images/avator.png'" alt="Hosts Avatar">
                            </div>
                            <div class="hosts-details">
                                <h3>Sellers name</h3> 
                            </div>
                        </div>
                        <hr>
                        <div class="tfhb-hosts-info tfhb-flexbox">  
                            <span class="tfhb-info-icon-text tfhb-flexbox" > <Icon name="Mail" size=20 />sellers@sellers.com</span>
                            <span class="tfhb-info-icon-text tfhb-flexbox" > <Icon name="Phone" size=20 />000000000000000</span>
                        </div>
                        <!-- <button class="tfhb-single-hosts-action"><Icon name="ListCollapse" size=20 /></button> -->
                    <div  class="tfhb-single-hosts-action tfhb-dropdown">
                        <img :src="$tfhb_url+'/assets/images/more-vertical.svg'" alt=""> 
                    </div>
                    </div> 
                </div>
                <!-- Single Hosts -->
                <!-- Single Hosts -->
                <div class="tfhb-single-hosts"> 
                    <div class="tfhb-single-hosts-wrap ">
                        <span class="tfhb-hosts-status" >{{ $tfhb_trans('Active') }}</span> 
                        <!-- <span class="tfhb-hosts-status tfhb-hosts-status-warning" v-else>{{ $tfhb_trans('Disable') }}</span> -->

                        <div class="tfhb-hosts-info tfhb-flexbox">
                            <div class="hosts-avatar" >
                                <img   :src="$tfhb_url+'/assets/images/avator.png'" alt="Hosts Avatar">
                            </div>
                            <div class="hosts-details">
                                <h3>Sellers name</h3> 
                            </div>
                        </div>
                        <hr>
                        <div class="tfhb-hosts-info tfhb-flexbox">  
                            <span class="tfhb-info-icon-text tfhb-flexbox" > <Icon name="Mail" size=20 />sellers@sellers.com</span>
                            <span class="tfhb-info-icon-text tfhb-flexbox" > <Icon name="Phone" size=20 />000000000000000</span>
                        </div>
                        <!-- <button class="tfhb-single-hosts-action"><Icon name="ListCollapse" size=20 /></button> -->
                    <div  class="tfhb-single-hosts-action tfhb-dropdown">
                        <img :src="$tfhb_url+'/assets/images/more-vertical.svg'" alt=""> 
                    </div>
                    </div> 
                </div>
                <!-- Single Hosts -->
                <!-- Single Hosts -->
                <div class="tfhb-single-hosts"> 
                    <div class="tfhb-single-hosts-wrap ">
                        <span class="tfhb-hosts-status" >{{ $tfhb_trans('Active') }}</span> 
                        <!-- <span class="tfhb-hosts-status tfhb-hosts-status-warning" v-else>{{ $tfhb_trans('Disable') }}</span> -->

                        <div class="tfhb-hosts-info tfhb-flexbox">
                            <div class="hosts-avatar" >
                                <img   :src="$tfhb_url+'/assets/images/avator.png'" alt="Hosts Avatar">
                            </div>
                            <div class="hosts-details">
                                <h3>Sellers name</h3> 
                            </div>
                        </div>
                        <hr>
                        <div class="tfhb-hosts-info tfhb-flexbox">  
                            <span class="tfhb-info-icon-text tfhb-flexbox" > <Icon name="Mail" size=20 />sellers@sellers.com</span>
                            <span class="tfhb-info-icon-text tfhb-flexbox" > <Icon name="Phone" size=20 />000000000000000</span>
                        </div>
                        <!-- <button class="tfhb-single-hosts-action"><Icon name="ListCollapse" size=20 /></button> -->
                    <div  class="tfhb-single-hosts-action tfhb-dropdown">
                        <img :src="$tfhb_url+'/assets/images/more-vertical.svg'" alt=""> 
                    </div>
                    </div> 
                </div>
                <!-- Single Hosts -->
                <!-- Single Hosts -->
                <div class="tfhb-single-hosts"> 
                    <div class="tfhb-single-hosts-wrap ">
                        <span class="tfhb-hosts-status" >{{ $tfhb_trans('Active') }}</span> 
                        <!-- <span class="tfhb-hosts-status tfhb-hosts-status-warning" v-else>{{ $tfhb_trans('Disable') }}</span> -->

                        <div class="tfhb-hosts-info tfhb-flexbox">
                            <div class="hosts-avatar" >
                                <img   :src="$tfhb_url+'/assets/images/avator.png'" alt="Hosts Avatar">
                            </div>
                            <div class="hosts-details">
                                <h3>Sellers name</h3> 
                            </div>
                        </div>
                        <hr>
                        <div class="tfhb-hosts-info tfhb-flexbox">  
                            <span class="tfhb-info-icon-text tfhb-flexbox" > <Icon name="Mail" size=20 />sellers@sellers.com</span>
                            <span class="tfhb-info-icon-text tfhb-flexbox" > <Icon name="Phone" size=20 />000000000000000</span>
                        </div>
                        <!-- <button class="tfhb-single-hosts-action"><Icon name="ListCollapse" size=20 /></button> -->
                    <div  class="tfhb-single-hosts-action tfhb-dropdown">
                        <img :src="$tfhb_url+'/assets/images/more-vertical.svg'" alt=""> 
                    </div>
                    </div> 
                </div>
                <!-- Single Hosts -->
                
            </div>
        </div>


    </div> 
</template>  
<style scoped lang="scss">
.tfhb-admin-hosts{
    padding: 16px;
    background-color: #fff;
    border-radius: 16px;
    .tfhb-hosts-content {
        margin-top: 0;
    }
}

</style>