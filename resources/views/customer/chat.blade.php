@extends('layouts.admin')

@section('title', 'Customer Chat')

@section('content')
<div class="row">
    <div class="col-md-12">
        <section class="box box-danger">
            <div class="box-body">
              <div id="vue-chat-element">
                <!-- <h3 class=" text-center">Messaging</h3> -->
                <div class="messaging">
                  <div class="inbox_msg">
                    <div class="inbox_people">
                      <div class="headind_srch">
                        <div class="recent_heading">
                          <h4>Inbox</h4>
                          <!-- <select class="form-control">
                            <option value="">Product</option>
                            <option value="">Umum</option>
                          </select> -->
                        </div>
                        <div class="srch_bar">
                          <!-- <div class="stylish-input-group">
                            <input type="text" class="search-bar"  placeholder="Search" >
                            <span class="input-group-addon">
                            <button type="button"> <i class="fa fa-search" aria-hidden="true"></i> </button>
                            </span> </div> -->
                        </div>
                      </div>
                      <div class="inbox_chat">

                        <div :class="['chat_list', {'active_chat': isOpenInbox && ibx.id === inboxContentHeader.id }]" v-for="ibx in inbox" :key="ibx.id" @click="openInbox(ibx.id)">
                          <div class="chat_people">
                            <div class="chat_img"> <img src="https://ptetutorials.com/images/user-profile.png" alt="user-profile"> </div>
                            <div class="chat_ib">
                              <label class="label label-default">@{{ ibx.chat_type.name }}</label>
                              <!-- <i class="new_ib float-right fa fa-envelope text-danger" style="margin-top: 5px"></i> -->
                              <h5>@{{ ibx.customer.name || ibx.customer.email }}
                                <span class="chat_date" v-if="Object.entries(ibx.last_messages).length > 0">@{{ ibx.last_messages.created_at | toMonthDate }}</span>
                              </h5>
                              <p>@{{
                                Object.entries(ibx.last_messages).length > 0 ? ibx.last_messages.message : ''
                              }}</p>
                            </div>
                          </div>
                        </div>

                        <infinite-loading @infinite="fetchInbox">
                          <div slot="no-more">No more message...</div>
                        </infinite-loading>

                      </div>
                    </div>
                    <div class="mesgs">
                      <div class="headind_srch">
                        <div class="msg_heading">
                          <div class="chat_people" v-if="isOpenInbox">
                            <div class="chat_img"> <img src="https://ptetutorials.com/images/user-profile.png" alt="sunil"> </div>
                            <div class="chat_ib">
                              <h5>@{{ inboxContentHeader.customer.name || inboxContentHeader.customer.email }}
                              <label class="pull-right label label-default">@{{ inboxContentHeader.chat_type.name }}</label></h5>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="msg_history" ref="msg_history" style="padding-top: 40px;">
                        <div class="loading-overlay" :style="{visibility: isSendMessage ? 'visible' : 'hidden', opacity: 0.5}">
                            <div class="bounce-loader">
                                <div class="bounce1"></div>
                                <div class="bounce2"></div>
                                <div class="bounce3"></div>
                            </div>
                        </div>

                        <infinite-loading v-if="isOpenInbox" :identifier="inboxContentHeader.id" @infinite="fetchInboxContent" style="margin-bottom:15px;">
                          <div slot="no-more">No more message...</div>
                        </infinite-loading>

                        <div v-for="msg in reverseInboxContent" :key="`msg-${inboxContentHeader.id}-${msg.id}`">

                          <div class="incoming_msg" v-if="msg.sender == 1">
                            <div class="incoming_msg_img"> <img src="https://ptetutorials.com/images/user-profile.png" alt="sunil"> </div>
                            <div class="received_msg">
                              <div class="received_withd_msg">
                                <p> @{{ msg.message }}</p>
                                <span class="time_date">@{{ msg.created_at | toHour }}   |   @{{ msg.created_at | toMonthDate }}</span>
                              </div>
                            </div>
                          </div>

                          <div class="outgoing_msg" v-else>
                            <div class="sent_msg">
                              <p>@{{ msg.message }}</p>
                              <span class="time_date">@{{ msg.created_at | toHour }}   |   @{{ msg.created_at | toMonthDate }}</span>
                            </div>
                          </div>

                        </div>
                      </div>
                      <div class="type_msg">
                        <div class="input_msg_write">
                          <input type="text" class="write_msg" v-model="message" placeholder="Type a message" :disabled="isSendMessage" />
                          <button class="msg_send_btn" @click="sendMessage()" :disabled="isSendMessage"><i class="fa fa-paper-plane" aria-hidden="true"></i></button>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- <p class="text-center top_spac"> Design by <a target="_blank" href="#">Sunil Rajput</a></p> -->
                </div>
              </div>
            </div>
        </section>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/utomo-chat.css') }}">
@stop

@section('js')
<script src="{{ asset('js/utomo-chat.js') }}"></script>
@stop