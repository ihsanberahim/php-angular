'use strict';

/**
 * @ngdoc function
 * @name ihsanBerahimcomApp.controller:RootCtrl
 * @description
 * # RootCtrl
 * Controller of the ihsanBerahimcomApp
 */
angular.module('ihsanBerahimcomApp')
  .controller('RootCtrl', function ($scope, $state, $stateParams) {
  		$state.go('home');
  });
