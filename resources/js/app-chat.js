import InfiniteLoading from 'vue-infinite-loading';

const moment = require('moment');
const axios = require('axios');
const axiosRequest = axios.create({
  baseURL: `http://utomodeck-erp.local/ajax`
});

var newChatCustomer = false;

window.Vue = require('vue');

Vue.filter('toHour', function (value) {
  return moment(value).format("HH:mm")
})
Vue.filter('toMonthDate', function (value) {
  return moment(value).format("MMM D")
})

const appChat = new Vue({
    el: '#vue-chat-element',
    mounted () {
      // this.fetchInbox()

      // let self = this
      // window.OneSignal.push(function () {
      //   console.log(`called from vue..`)
      //   window.OneSignal.on('notificationDisplay', function(event) {
      //     document.querySelector("#NSalesChatAudio").play()
      //     self.openInbox(event.data.ChatHeaderId)

      //     console.log(`new message...`, event)
      //   });
      // })
    },
    components: { InfiniteLoading },
    computed: {
      isOpenInbox: function () {
        return Object.entries(this.inboxContentHeader).length !== 0
      },
      reverseInboxContent: function () {
        return this.inboxContent.sort(this.ascending('id'))
      }
    },
    data: function () {
      return {
        newChatCustomer: newChatCustomer,
        inboxPage: {
          current_page: 0
        },
        inbox: [],
        inboxContentPage: {
          current_page: 0
        },
        inboxContentHeader: {},
        inboxContent: [],
        message: '',
        isSendMessage: false
      }
    },
    methods: {
      ascending: function (key) {
        return function(a, b) {
          if(!a.hasOwnProperty(key) || !b.hasOwnProperty(key)) {
            // property doesn't exist on either object
            return 0;
          }

          let comparison = 0;
          if (a[key] > b[key]) {
            comparison = 1;
          } else if (a[key] < b[key]) {
            comparison = -1;
          }

          return comparison
        };
      },
      sendMessage: function ()
      {
        if(this.message === '') return;

        const params = { message: this.message }
        this.isSendMessage = true
        axiosRequest.post(`/customer-chat/${this.inboxContentHeader.id}/message`, params)
           .then((response) => {
             let updateInboxContent = [response.data]
             updateInboxContent.push(...this.inboxContent)
             this.inboxContent = updateInboxContent
             this.isSendMessage = false
             this.message = ''

             this.toLastMessage()
           })
      },
      toLastMessage: function ()
      {
        this.$nextTick(function () {
          const lastMessage = this.$refs.msg_history.lastElementChild
          this.$refs.msg_history.scrollTop = lastMessage.offsetTop
        })
      },
      openInbox: function (id)
      {
        if (this.inboxContentHeader.id === id) return;

        this.inboxContentHeader = this.inbox.find(ibx => ibx.id === id)
        this.inboxContentPage.current_page = 0
        this.inboxContent = []
      },
      fetchInboxContent: function ($state)
      {
        const page = this.inboxContentPage.current_page+1
        axiosRequest.get(`/customer-chat/${this.inboxContentHeader.id}/message?page=${page}`)
             .then(response => {
               if (response.data.data.length) {
                 this.inboxContent.push(...response.data.data)

                 this.inboxContentPage = response.data
                 delete this.inboxContentPage.data
                 $state.loaded()

                 if (page === 1) this.toLastMessage();

                 return
               }

               $state.complete()
             })
      },
      fetchInbox: function ($state)
      {
        const page = this.inboxPage.current_page+1
        axiosRequest.get(`/customer-chat?page=${page}`)
             .then(response => {
               if (response.data.data.length) {
                 this.inbox.push(...response.data.data)

                 this.inboxPage = response.data
                 delete this.inboxPage.data
                 $state.loaded()

                 return
               }

               $state.complete()
             })
      },
    }
});
