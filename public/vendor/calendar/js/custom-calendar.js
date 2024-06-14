let CustomCalendar = {
	init: function(events) {
		let date = new Date();
		let d = date.getDate();
		let m = date.getMonth();
		let y = date.getFullYear();

		$('#calendar').fullCalendar({
			header: {
				left: 'prev, next',
				center: 'title',
				right: 'today, month, agendaWeek, agendaDay'
			},
			locale: 'ru',
			events: events.map(function(event) {
				return {
					title: event.title,
					start: new Date(event.start),
					end: new Date(event.end)
				};
			}),
			editable: false,
			eventLimit: true,
			droppable: false,
			displayEventTime: false
		});
	}
};
