import pandas as pd
from sklearn.ensemble import GradientBoostingRegressor
from sklearn.model_selection import RandomizedSearchCV
from sklearn.metrics import make_scorer, mean_squared_error
from joblib import dump, Parallel, delayed
from schedule_project.script_date import month_from_week, season_from_month

def non_negative_error(y_true, y_pred):
    return mean_squared_error(y_true, y_pred)

def train_model(data):
    try:
        df = pd.DataFrame(data)

        for col in df.columns:
            df[col] = df[col].apply(lambda x: int(float(x)) if pd.notnull(x) else 0)

        df['Month'] = df.apply(lambda row: month_from_week(row['Year'], row['Week']), axis=1)
        df['Season'] = df['Month'].apply(season_from_month)

        df = pd.get_dummies(df, columns=['Season'])

        X = df[['Year', 'Week', 'Month'] + [col for col in df.columns if 'Season_' in col]]
        y_columns = [
            'count_ct', 'count_mri', 'count_rg', 'count_flg', 'count_mmg', 'count_densitometer',
            'count_ct_with_ku_1_zone', 'count_mri_with_ku_1_zone', 'count_ct_with_ku_more_than_1_zone', 'count_mri_with_ku_more_than_1_zone'
        ]

        models = {}

        param_dist = {
            'n_estimators': [100, 200, 500],
            'learning_rate': [0.05, 0.1, 0.2],
            'max_depth': [3, 4, 5],
            'min_samples_split': [2, 5, 10],
            'min_samples_leaf': [1, 2, 4],
            'subsample': [0.8, 0.9, 1.0]
        }

        def train_single_model(column):
            mask = df[column] != 0
            X_filtered = X[mask]
            y_filtered = df[column][mask]

            model = GradientBoostingRegressor(random_state=42)
            random_search = RandomizedSearchCV(estimator=model, param_distributions=param_dist, n_iter=10, cv=4, n_jobs=-1, scoring=make_scorer(non_negative_error), random_state=42)
            random_search.fit(X_filtered, y_filtered)
            best_model = random_search.best_estimator_

            return column, best_model

        results = Parallel(n_jobs=-1)(delayed(train_single_model)(column) for column in y_columns)

        for column, best_model in results:
            models[column] = best_model

        for key, model in models.items():
            model_path = f"./storage/{key}_model.pkl"
            dump(model, model_path)

        return "success"

    except Exception as e:
        return str(e)
