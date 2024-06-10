# This is an auto-generated Django model module.
# You'll have to do the following manually to clean this up:
#   * Rearrange models' order
#   * Make sure each model has one field with primary_key=True
#   * Make sure each ForeignKey and OneToOneField has `on_delete` set to the desired behavior
#   * Remove `managed = False` lines if you wish to allow Django to create, modify, and delete the table
# Feel free to rename the models, but don't rename db_table values or field names.
from django.db import models

class Calendar(models.Model):
    calendar_id = models.AutoField(primary_key=True)
    rvd = models.CharField(max_length=255, blank=True, null=True, db_comment='Наименование')
    sql_date = models.DateField(blank=True, null=True, db_comment='Дата')
    day_of_month = models.IntegerField(blank=True, null=True, db_comment='Порядковый номер дня в текущем месяце')
    day_of_year = models.IntegerField(blank=True, null=True, db_comment='Порядковый номер дня в текущем году')
    week_of_year = models.IntegerField(blank=True, null=True, db_comment='Порядковый номер недели в текущем году')
    month_of_year = models.IntegerField(blank=True, null=True, db_comment='Порядковый номер месяца в текущем году')
    month_name = models.CharField(max_length=10, blank=True, null=True, db_comment='Название месяца')
    god = models.IntegerField(blank=True, null=True, db_comment='Год')
    quarter = models.IntegerField(blank=True, null=True, db_comment='Квартал')
    half_year = models.IntegerField(blank=True, null=True, db_comment='Полугодие')
    holiday = models.IntegerField(blank=True, null=True, db_comment='Признак: 1- выходной/праздн.день, 0 -рабочий день')
    day_of_week = models.IntegerField(blank=True, null=True, db_comment='Порядковый номер дня в текущей недели')
    end_of_month = models.IntegerField(blank=True, null=True, db_comment='Признак: 1-последний день месяца, 0 -в остальных случаях')
    work_day_of_year = models.IntegerField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'calendar'
        db_table_comment = 'Производственный календарь'


class CoefficientPlan(models.Model):
    minimal_coefficient_per_shift = models.FloatField(blank=True, null=True, db_comment='Минимальный план  УЕ* в смену')
    max_coefficient_per_shift = models.FloatField(blank=True, null=True, db_comment='Максимальный план  УЕ* в смену')
    minimal_coefficient_per_month = models.FloatField(blank=True, null=True, db_comment='Минимальный план  УЕ* в месяц')
    max_coefficient_per_month = models.FloatField(blank=True, null=True, db_comment='Максимальный план  УЕ* в месяц')

    class Meta:
        managed = False
        db_table = 'coefficient_plan'


class Competencies(models.Model):
    modality = models.CharField(max_length=255, blank=True, null=True, db_comment='Модальность')
    contrast = models.CharField(max_length=255, blank=True, null=True, db_comment='Контрастное усиление')
    minimal_count_per_shift = models.FloatField(blank=True, null=True, db_comment='Минимальное количество исследований за смену шт. ')
    minimal_coefficient_per_shift = models.FloatField(blank=True, null=True, db_comment='Минимальное количество УЕ за смену шт.')
    max_count_per_shift = models.FloatField(blank=True, null=True, db_comment='Максимальное количество исследований за смену шт.')
    max_coefficient_per_shift = models.FloatField(blank=True, null=True, db_comment='Максимальное количество УЕ за смену, с округлением вниз до целого числа')
    coefficient = models.FloatField(blank=True, null=True, db_comment='Количество УЕ в одном описании')

    class Meta:
        managed = False
        db_table = 'competencies'


class Doctor(models.Model):
    surname = models.CharField(max_length=255, blank=True, null=True, db_comment='Фамилия')
    firstname = models.CharField(max_length=255, blank=True, null=True, db_comment='Имя')
    middlename = models.CharField(max_length=255, blank=True, null=True, db_comment='Отчество')
    addon_competencies = models.JSONField()
    main_competencies = models.JSONField()
    stavka = models.FloatField()

    class Meta:
        managed = False
        db_table = 'doctor'


class DoctorWorkSchedules(models.Model):
    type = models.CharField(max_length=255, blank=True, null=True, db_comment='Тип смены')
    hours_per_shift = models.IntegerField(blank=True, null=True, db_comment='Количество часов за смену')
    shift_per_cycle = models.IntegerField(blank=True, null=True, db_comment='Смен за цикл')
    days_off = models.IntegerField(blank=True, null=True, db_comment='Количество выходных дней за цикл')
    doctor = models.OneToOneField(Doctor, models.DO_NOTHING, blank=True, null=True, related_name='work_schedule')

    class Meta:
        managed = False
        db_table = 'doctor_work_schedules'


class DoctrineMigrationVersions(models.Model):
    version = models.CharField(primary_key=True, max_length=191)
    executed_at = models.DateTimeField(blank=True, null=True)
    execution_time = models.IntegerField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'doctrine_migration_versions'


class Privilege(models.Model):
    code = models.CharField(max_length=255)
    name = models.CharField(max_length=255)

    class Meta:
        managed = False
        db_table = 'privilege'


class Role(models.Model):
    code = models.CharField(max_length=255)
    name = models.CharField(max_length=255)
    privileges = models.TextField()  # This field type is a guess.

    class Meta:
        managed = False
        db_table = 'role'


class Studies(models.Model):
    date = models.DateTimeField(blank=True, null=True)
    competency = models.ForeignKey(Competencies, models.DO_NOTHING, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'studies'


class TempDoctorSchedule(models.Model):
    date = models.DateField()
    start_work_time = models.CharField(max_length=255, blank=True, null=True)
    end_work_time = models.CharField(max_length=255, blank=True, null=True)
    doctor = models.ForeignKey(Doctor, models.DO_NOTHING, blank=True, null=True)
    temp_schedule = models.ForeignKey('TempScheduleWeekStudies', models.DO_NOTHING, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'temp_doctor_schedule'


class TempSchedule(models.Model):
    id = models.AutoField(primary_key=True)
    created_at = models.DateTimeField()

    class Meta:
        managed = False
        db_table = 'temp_schedule'


class TempScheduleWeekStudies(models.Model):
    empty = models.IntegerField()
    week_studies = models.ForeignKey('WeekStudies', models.DO_NOTHING, blank=True, null=True)
    temp_schedule = models.ForeignKey(TempSchedule, models.DO_NOTHING, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'temp_schedule_week_studies'


class User(models.Model):
    username = models.CharField(unique=True, max_length=180)
    roles = models.TextField()  # This field type is a guess.
    password = models.CharField(max_length=255)
    privileges = models.TextField()  # This field type is a guess.

    class Meta:
        managed = False
        db_table = 'user'


class WeekStudies(models.Model):
    week_number = models.IntegerField()
    year = models.IntegerField()
    count = models.IntegerField()
    competency = models.ForeignKey(Competencies, models.DO_NOTHING, blank=True, null=True)
    start_of_week = models.DateField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'week_studies'
