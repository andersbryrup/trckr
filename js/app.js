angular.module('trackers',  ['ui.bootstrap'])
  .factory('Trackers', function($rootScope, $http, $log, $q){
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
        var deferred = $q.defer();
        $http.get('ajax.php?q=getTrackerInfo&id='+tracker.id).success(function(data) {
          deferred.resolve(data);
        });
        return deferred.promise;
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
          //$rootScope.$broadcast("overviewUpdate");
        });
      }
    };
  })
  .directive('typeaheadtagger', function($log){
    return {
      link: function(scope, element, attrs) {
        element.addTagger(attrs.typeaheadtagger);

        element.bind('tagger:select', function(object, data){
          scope.$apply(function() {
            scope.tracker.input = data;
          });
        });
      }
    };
  });


function ListCtrl($scope, $modal, Trackers, $timeout, $log){
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

  $scope.timeTotal = function(trackers){
    if(typeof trackers === 'object'){
      var total = null;
      angular.forEach(trackers, function(tracker){
        splitTime = tracker.time.split(':');
        total += parseInt(splitTime[0] * 60);
        total += parseInt(splitTime[1]);
      });

      hours = Math.floor(total / 60);
      minutes = total % 60;

      if(hours < 10){
        hours = '0' + hours;
      }
      if(minutes < 10){
        minutes = '0' + minutes;
      }


      return hours + ':' + minutes;
    }
  }

  $scope.edit = function(tracker) {

    var editTracker = tracker;

    var modalInstance = $modal.open({
      templateUrl: 'tpl/editTrackerModal.html',
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
  var today = new Date();
  var yesterday = new Date();
  
  today.setHours(23, 59, 0, 0);

  yesterday.setDate(yesterday.getDate() -1);
  yesterday.setHours(0,0,0,0);

  $scope.overview = {
    to: today,
    from: yesterday
  };

  // Initial load
  updateOverview();

  $scope.register = function(tracker){
    Trackers.register(tracker);
    
    // This is when it got a little wierd.
    Trackers.getinfo(tracker).then(function(data){
      // For some reason we have to do this
      tracker.register_status = data.register_status;
      tracker.register_message = data.register_message;
    });
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

function NavCtrl($scope, $modal){
  $scope.syncClients = function(){
    
    var modalInstance = $modal.open({
      templateUrl: 'tpl/syncClientsModal.html',
      controller: SyncClientsCtrl,
      backdrop: 'static'
    });
  }
}

function SyncClientsCtrl($scope, $http, $modalInstance, $timeout){
  $http.get('ajax.php?q=syncClients').then(function(result) {
    $scope.message = result.data.msg;
    $timeout(function(){
      $modalInstance.close();
    }, 1000);
  });
}
