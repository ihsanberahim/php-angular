'use strict';

/**
 * @ngdoc overview
 * @name ihsanBerahimcomApp
 * @description
 * # ihsanBerahimcomApp
 *
 * Main module of the application.
 */
angular
  .module('ihsanBerahimcomApp', [
    'ngAnimate',
    'ngCookies',
    'ngResource',
    'ngRoute',
    'ngSanitize',
    'ngTouch',
    'ui.router'
  ])
  .config(function ($routeProvider, $stateProvider, $urlRouterProvider, $urlMatcherFactoryProvider, $locationProvider) {
    $urlMatcherFactoryProvider.strictMode(false);
    $locationProvider.html5Mode({
      enabled: true,
      requireBase: false
    });
    
    $stateProvider
        .state('root', {
          url: '/',
          views: {
            '': {
              templateUrl: 'views/master.layout.html',
              controller: 'RootCtrl'
            }
          }
        })
        .state('home', {
          parent: 'root',
          url: 'home',
          views: {
            '': {
              templateUrl: 'views/home.html',
            }
          }
        })
        .state('otherwise', {
            url: '*path',
            templateUrl: 'views/404.layout.html'
        });

    $urlRouterProvider.when('','/');
  });
