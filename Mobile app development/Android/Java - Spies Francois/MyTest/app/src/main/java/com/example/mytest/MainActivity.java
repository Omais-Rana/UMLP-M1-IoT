package com.example.mytest;

import androidx.appcompat.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;

public class MainActivity extends AppCompatActivity {

    EditText InitialPrice, Tax, FullPrice;
    Button calculate;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        // Connect variables with layout elements
        InitialPrice = findViewById(R.id.InitialPrice);
        Tax = findViewById(R.id.Tax);
        FullPrice = findViewById(R.id.FullPrice);
        calculate = findViewById(R.id.calculate);

        // When button is clicked
        calculate.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                // Get input values
                String priceText = InitialPrice.getText().toString();
                String taxText = Tax.getText().toString();

                if (priceText.isEmpty() || taxText.isEmpty()) {
                    Toast.makeText(MainActivity.this, "Please enter both fields", Toast.LENGTH_SHORT).show();
                    return;
                }

                double price = Double.parseDouble(priceText);
                double tax = Double.parseDouble(taxText);

                // Calculate final price = price + (price * tax / 100)
                double total = price + (price * tax / 100);

                // Display result
                FullPrice.setText(String.format("%.2f", total));
            }
        });
    }
}
