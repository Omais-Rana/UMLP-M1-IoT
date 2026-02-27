import pandas as pd
import numpy as np
import matplotlib.pyplot as plt
from sklearn.datasets import load_iris

# ==========================================
# PART 1 - Loading and Exploration
# ==========================================
print("--- PART 1: LOADING & EXPLORATION ---")

# 1. Import Iris dataset
iris_data = load_iris(as_frame=True)

# 2. Build DataFrame with specific column names
df = iris_data.frame
df = df.rename(columns={
    'sepal length (cm)': 'SepalLengthCm',
    'sepal width (cm)': 'SepalWidthCm',
    'petal length (cm)': 'PetalLengthCm',
    'petal width (cm)': 'PetalWidthCm',
    'target': 'Species'
})

# Map target integers to species names for clarity
df['Species'] = df['Species'].map({0: 'setosa', 1: 'versicolor', 2: 'virginica'})

# 3. Display first 5 and last 5 rows
print("\nFirst 5 rows:")
print(df.head())
print("\nLast 5 rows:")
print(df.tail())

# 4. Display shape
print(f"\nDataFrame Shape: {df.shape}")

# 5. Display info and describe
print("\n--- Info ---")
print(df.info())
print("\n--- Describe ---")
print(df.describe())

# 6. Display data types
print("\n--- Data Types ---")
print(df.dtypes)


# ==========================================
# PART 2 - Selection and Indexing
# ==========================================
print("\n--- PART 2: SELECTION & INDEXING ---")

# 1. Select only specific columns
subset_cols = df[['SepalLengthCm', 'PetalLengthCm']]
print(f"Subset columns shape: {subset_cols.shape}")

# 2. Use loc[] for rows 10-20 (inclusive) and specific columns
# Note: loc includes the end index
loc_subset = df.loc[10:20, ['Species', 'PetalWidthCm']]
print("\nloc[10:20] subset head:")
print(loc_subset.head())

# 3. Use iloc[] for first 5 rows, first 2 columns
iloc_subset = df.iloc[:5, :2]
print("\niloc[:5, :2] subset:")
print(iloc_subset)

# 4. Filter SepalLengthCm > 6.0
filter_1 = df[df['SepalLengthCm'] > 6.0]

# 5. Filter multiple conditions
filter_2 = df[(df['SepalLengthCm'] > 5.5) & (df['PetalLengthCm'] < 4.0)]

# 6. Use query() method for versicolor
versicolor_df = df.query("Species == 'versicolor'")
print(f"\nVersicolor count via query: {len(versicolor_df)}")


# ==========================================
# PART 3 - Column and Row Manipulation
# ==========================================
print("\n--- PART 3: MANIPULATION ---")

# 1. Add PetalRatio
df['PetalRatio'] = df['PetalLengthCm'] / df['PetalWidthCm']

# 2. Add SepalArea
df['SepalArea'] = df['SepalLengthCm'] * df['SepalWidthCm']

# 3. Create categorical column 'Taille'
conditions = [
    (df['SepalLengthCm'] < 5.0),
    (df['SepalLengthCm'] >= 5.0) & (df['SepalLengthCm'] < 6.5)
]
choices = ['small', 'medium']
# np.select is efficient for multiple conditions; default is 'large'
df['Taille'] = np.select(conditions, choices, default='large')

print("Categorical column 'Taille' created.")
print(df['Taille'].value_counts())

# 4. Drop SepalArea column
df.drop(columns=['SepalArea'], inplace=True)

# 5. Drop rows where SepalLengthCm < 5.0
df = df[df['SepalLengthCm'] >= 5.0]

# 6. Sort by PetalLengthCm descending
df = df.sort_values(by='PetalLengthCm', ascending=False)

# 7. Reset index
df.reset_index(drop=True, inplace=True)
print("\nIndex reset. New shape:", df.shape)


# ==========================================
# PART 4 - Missing Values Handling
# ==========================================
print("\n--- PART 4: MISSING VALUES ---")

# 1. Introduce NaN in 5 random cells of PetalWidthCm
random_indices = df.sample(5, random_state=42).index
df.loc[random_indices, "PetalWidthCm"] = np.nan

# 2. Count missing values per column
print("Missing values per column:")
print(df.isna().sum())

# 3. Display rows containing NaN
print("\nRows with NaN:")
print(df[df.isna().any(axis=1)])

# 4. Replace NaN with column mean
# Create a clean copy by dropping NaNs FIRST
df_clean = df.dropna()
print(f"Shape after dropping NaNs: {df_clean.shape}")

# THEN replace NaNs with column mean in the original df
mean_val = df['PetalWidthCm'].mean()
df['PetalWidthCm'] = df['PetalWidthCm'].fillna(mean_val)

# ==========================================
# PART 5 - Aggregations and Groupby
# ==========================================
print("\n--- PART 5: AGGREGATIONS ---")

# 1. Count occurrences per species
print(df['Species'].value_counts())

# 2. Mean of numeric variables per species
# We select only numeric columns for the mean calculation
numeric_cols = df.select_dtypes(include=[np.number]).columns
print("\nMean per species:")
print(df.groupby("Species")[numeric_cols].mean())

# 3. Multiple statistics per species
print("\nDetailed stats per species:")
print(df.groupby("Species")[['PetalLengthCm', 'SepalLengthCm']].agg(["mean", "std", "min", "max"]))

# 4. Percentiles for PetalLengthCm
def percentiles(x):
    return x.quantile([0.25, 0.5, 0.75])

print("\nPercentiles (25%, 50%, 75%) for PetalLengthCm:")
print(df.groupby("Species")['PetalLengthCm'].apply(percentiles))

# 5. Crosstab between Species and Taille
print("\nCrosstab Species vs Taille:")
print(pd.crosstab(df['Species'], df['Taille']))


# ==========================================
# PART 6 - Reading/Writing Files
# ==========================================
print("\n--- PART 6: FILE I/O ---")

# 1. Export to CSV without index
df.to_csv("iris_enrichi.csv", index=False)

# 2. Reload
df_reloaded = pd.read_csv("iris_enrichi.csv")

# 3. Verify data identical (checking shape and columns)
print(f"Original shape: {df.shape}, Reloaded shape: {df_reloaded.shape}")

# 4. Export with separator ;
df.to_csv("iris_semicolon.csv", sep=';', index=False)
df_semi = pd.read_csv("iris_semicolon.csv", sep=';')
print("Semicolon CSV read success.")


# ==========================================
# PART 7 - Visualizations
# ==========================================
print("\n--- PART 7: VISUALIZATIONS ---")

# 7.1 Histogram of SepalLengthCm
plt.figure(figsize=(6, 4))
plt.hist(df['SepalLengthCm'], bins=15, color='skyblue', edgecolor='black')
plt.title('7.1 Histogram of SepalLengthCm')
plt.xlabel('Sepal Length')
plt.ylabel('Frequency')
plt.show()

# 7.2 Simple scatter plot
plt.figure(figsize=(6, 4))
plt.scatter(df['SepalLengthCm'], df['PetalLengthCm'], alpha=0.6)
plt.title('7.2 Sepal vs Petal Length')
plt.xlabel('Sepal Length')
plt.ylabel('Petal Length')
plt.show()

# 7.3 Scatter plot colored by species
plt.figure(figsize=(8, 5))
colors = {'setosa': 'red', 'versicolor': 'green', 'virginica': 'blue'}
# Loop through species to plot them with correct labels
for species, color in colors.items():
    subset = df[df['Species'] == species]
    plt.scatter(subset['SepalLengthCm'], subset['PetalLengthCm'], 
                label=species, color=color, alpha=0.6)
plt.title('7.3 Sepal vs Petal Length by Species')
plt.xlabel('Sepal Length')
plt.ylabel('Petal Length')
plt.legend()
plt.show()

# 7.4 Boxplots by species
# Prepare list of data arrays for matplotlib
data_to_plot = [df[df['Species'] == s]['PetalLengthCm'] for s in df['Species'].unique()]
plt.figure(figsize=(8, 5))
plt.boxplot(data_to_plot, tick_labels=df['Species'].unique())
plt.title('7.4 Boxplot of Petal Length by Species')
plt.ylabel('Petal Length')
plt.show()

# 7.5 Bar chart (Count per species)
counts = df['Species'].value_counts()
plt.figure(figsize=(6, 4))
plt.bar(counts.index, counts.values, color=['gold', 'teal', 'orchid'])
plt.title('7.5 Flower Count per Species')
plt.show()

# 7.6 Correlation Matrix Heatmap
plt.figure(figsize=(6, 5))
corr_matrix = df[numeric_cols].corr()
plt.imshow(corr_matrix, cmap='coolwarm', interpolation='none')
plt.colorbar()
plt.xticks(range(len(corr_matrix)), corr_matrix.columns, rotation=45)
plt.yticks(range(len(corr_matrix)), corr_matrix.columns)
plt.title('7.6 Correlation Matrix')
plt.tight_layout()
plt.show()

# 7.7 (Bonus) Multiple Subplots
fig, axs = plt.subplots(2, 2, figsize=(10, 8))
cols_to_plot = ['SepalLengthCm', 'SepalWidthCm', 'PetalLengthCm', 'PetalWidthCm']

# Flatten the 2x2 matrix of axes to a 1D array for easy looping
axs = axs.flatten()

for i, col in enumerate(cols_to_plot):
    axs[i].hist(df[col], bins=15, color='lightgreen', edgecolor='black')
    axs[i].set_title(f'Dist. of {col}')

plt.suptitle('7.7 Histograms of Numeric Variables')
plt.tight_layout()
plt.show()


# ==========================================
# PART 8 - Bonus Exercises
# ==========================================
print("\n--- PART 8: BONUS ---")

# 1. Outlier: Largest PetalRatio
max_ratio_idx = df['PetalRatio'].idxmax()
outlier = df.loc[max_ratio_idx]
print("Flower with largest PetalRatio:")
print(outlier[['Species', 'PetalLengthCm', 'PetalWidthCm', 'PetalRatio']])

# 2. Coeff of Variation (std / mean)
stats = df.groupby('Species')['SepalLengthCm'].agg(['mean', 'std'])
stats['CV'] = stats['std'] / stats['mean']
print("\nCoefficient of Variation (SepalLength):")
print(stats)

# 3. Apply: "Typical" flower logic
# Calculate bounds for all numeric columns at once
numeric_cols = ['SepalLengthCm', 'SepalWidthCm', 'PetalLengthCm', 'PetalWidthCm']
means = df.groupby('Species')[numeric_cols].transform('mean')
stds = df.groupby('Species')[numeric_cols].transform('std')

# Create a boolean dataframe where True means the value is within bounds
is_typical = (df[numeric_cols] >= (means - stds)) & (df[numeric_cols] <= (means + stds))

# The flower is typical only if ALL numeric columns are True (axis=1 checks across the row)
df['Typical'] = is_typical.all(axis=1)

print("\nTypical vs Atypical count:")
print(df['Typical'].value_counts())