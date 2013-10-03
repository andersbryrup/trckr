angular.module('trackers',  ['ui.bootstrap'])
  .factory('Trackers', function($rootScope, $http, $log){
    var registerForm = function(){
      $form = null;

      if($form !== null){
        return $form;
      }
      
      $form = $http.get('ajax.php?q=getSubmitForm').then(function(result){
          return result.data;
      });
      return $form;

    };
    
    return {
      get : function() {
        return $http.get('ajax.php').then(function(result) {
          return result.data;
        });
      },
      getinfo : function(tracker) {
        return $http.get('ajax.php?q=getTrackerInfo&id='+tracker.id).then(function(result) {
          return result.data;
        });
      },
      save : function(tracker) {
        tracker.action = "saveTracker";

        $http.post('ajax.php', tracker).success(function(data){
          $rootScope.$broadcast("trackersUpdated");
        });
      }, 
      delete: function(tracker) {
        tracker.action = "deleteTracker";
        $http.post('ajax.php', tracker).success(function(data){
          $rootScope.$broadcast("trackersUpdated");
        });
      },
      start: function(tracker){
        tracker.action = "startTracker";
        $http.post('ajax.php', tracker).success(function(data){
          $rootScope.$broadcast("trackersUpdated");
        });
      },
      stop: function(tracker){
        tracker.action = "stopTracker";
        $http.post('ajax.php', tracker).success(function(data){
          $rootScope.$broadcast("trackersUpdated");
        });
      },
      overview: function(from, to){
        return $http.get('ajax.php?q=getOverview&from='+from +'&to='+to).then(function(result){
          return result.data;
        });
      },
      registerform: function(){
        $form = null;

        if($form !== null){
          return $form;
        }
        
        $form = $http.get('ajax.php?q=getSubmitForm').then(function(result){
            return result.data;
        });
        return $form;
      },
      register: function(tracker){
        tracker.action = "registerTracker";
        $http.post('ajax.php', tracker).success(function(data){
          $rootScope.$broadcast("overviewUpdate");
        });
      }
    };
  })
  .directive('typeaheadtagger', function($log){
    return {
      link: function(scope, element) {
        element.addTagger();

        element.bind('tagger:select', function(object, data){
          scope.$apply(function() {
            scope.tracker.input = data;
          });
        });
      }
    };
  });


function ListCtrl($scope, $modal, Trackers, $timeout){
  $scope.trackers = Trackers.get();

  $scope.$on('trackersUpdated', function() {
    $scope.trackers = Trackers.get();
  });

  var myIntervalFunction = function() {
      cancelRefresh = $timeout(function myFunction() {
          $scope.trackers = Trackers.get();
          cancelRefresh = $timeout(myIntervalFunction, 60000);
      },60000);
  };

  myIntervalFunction();


  $scope.edit = function(tracker) {

    var editTracker = tracker;

    var modalInstance = $modal.open({
      templateUrl: 'editTrackerModal.html',
      controller: ModalEditCtrl,
      resolve : {
        tracker : function(){
          return editTracker;
        }
      }
    });
  };

  $scope.start = function(tracker) {
    // Set tracker start, so the tracker is updated as active, right away
    // instead of waiting for the ajax callback.
    tracker.start = 1;
    Trackers.start(tracker);
  };

  $scope.stop = function(tracker) {
    // Same shit as before, unset tracker start, so it looks like the stuff
    // responds right away
    tracker.start = null;
    Trackers.stop(tracker);
  };
}

function CreateCtrl($scope, Trackers){
  $scope.save = function(){
    Trackers.save($scope.tracker);
    $scope.tracker.input = "";
  };
}

function ModalEditCtrl($scope, $modalInstance, tracker, Trackers) {
  $scope.tracker = tracker;

  $scope.ok = function () {
    Trackers.save($scope.tracker);
    $modalInstance.close();
  };

  $scope.delete = function () {
    Trackers.delete($scope.tracker);
    $modalInstance.close();
  };

  $scope.cancel = function () {
    $modalInstance.dismiss('cancel');
  };
}

function OverviewCtrl($scope, $timeout, $log, Trackers){
  // Helper function for loading overview
  var updateOverview = function(){
    $scope.trackers = Trackers.overview(
          Math.round($scope.overview.from.getTime()/1000), 
          Math.round($scope.overview.to.getTime()/1000)
        );
  };

  $scope.registerForm = Trackers.registerform();

  $scope.registerSuccess = function(status){
    if(status === 'success'){
      return true;
    }
    return false;
  };

  // Set up datepickers
  $scope.overview = {
    to: new Date(),
    from: (function(){this.setDate(this.getDate()-1); return this;}).call(new Date())
  };

  // Initial load
  updateOverview();

  $scope.register = function(tracker){
    Trackers.register(tracker);
    var info = Trackers.getinfo(tracker);
    tracker = info;
  };

  $scope.reloadOverview = function(){
    updateOverview();
  };
  // Various listeners for updating trackers
  // On changed values in datepicker
  $scope.$watch('overview.from', function(){
    updateOverview();
  });

  $scope.$watch('overview.to', function(){
    updateOverview();
  });
}
