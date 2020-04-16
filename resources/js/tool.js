Nova.booting((Vue, router, store) => {
  router.addRoutes([
    {
      name: 'nova-auth',
      path: '/nova-auth',
      component: require('./components/Tool'),
    },
  ])
})
