from django.db.models import Q
from .models import Doctor

class DoctorRepository:
    @staticmethod
    def find_by_ids(ids=None, exclude=None, modality=None, addon_modality=None):
        qs = Doctor.objects.all()

        if ids:
            qs = qs.filter(id__in=ids)

        if exclude:
            qs = qs.exclude(id__in=exclude)

        if modality:
            qs = qs.filter(main_competencies__contains=[modality])

        if addon_modality:
            qs = qs.filter(addon_competencies__contains=[addon_modality])

        return qs