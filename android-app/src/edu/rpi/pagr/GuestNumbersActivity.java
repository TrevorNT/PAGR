package edu.rpi.pagr;

import android.app.Activity;
import android.os.Bundle;
import android.widget.Button;

/**
 * Created by Daniel Zhao on 9/23/13.
 */
public class GuestNumbersActivity extends Activity {
    Button button_ok;

    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_guest_number);
        button_ok = (Button) findViewById(R.id.button_ok);
    }

}