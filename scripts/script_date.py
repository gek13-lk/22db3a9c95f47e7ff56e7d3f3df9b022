import datetime
import calendar

def get_weeks_in_month(year, month):
    start_date = datetime.date(year, month, 1)
    if month == 12:
        end_date = datetime.date(year + 1, 1, 1) - datetime.timedelta(days=1)
    else:
        end_date = datetime.date(year, month + 1, 1) - datetime.timedelta(days=1)

    weeks = []
    current_date = start_date
    next_week_num = None
    while current_date <= end_date:
        week_num = current_date.isocalendar()[1]

        if current_date.month == 1 and week_num == 52:
            current_year = year - 1
        else:
            current_year = year

        first_day_of_week = current_date - datetime.timedelta(days=current_date.weekday())
        last_day_of_week = first_day_of_week + datetime.timedelta(days=6)

        if first_day_of_week < start_date:
            first_day_of_week = start_date
        if last_day_of_week > end_date:
            last_day_of_week = end_date

        weeks.append({
            'Year': current_year,
            'Week': week_num
        })

        current_date = last_day_of_week + datetime.timedelta(days=1)

    return weeks

def month_from_week(year, week):
    first_day_of_week = datetime.datetime.strptime(f'{year}-W{week}-1', "%Y-W%U-%w")
    return first_day_of_week.month

def season_from_month(month):
    season_mapping = {
        12: 'Winter', 1: 'Winter', 2: 'Winter',
        3: 'Spring', 4: 'Spring', 5: 'Spring',
        6: 'Summer', 7: 'Summer', 8: 'Summer',
        9: 'Autumn', 10: 'Autumn', 11: 'Autumn'
    }
    return season_mapping[month]
