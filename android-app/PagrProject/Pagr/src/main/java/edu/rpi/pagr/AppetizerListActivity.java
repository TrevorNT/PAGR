package edu.rpi.pagr;

import java.util.ArrayList;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.AdapterView;
import android.widget.AdapterView.OnItemClickListener;
import android.widget.Button;
import android.widget.ListView;
import android.widget.Toast;

import com.actionbarsherlock.app.SherlockFragmentActivity;

import edu.rpi.pagr.misc.CustomListAdapter;
import edu.rpi.pagr.misc.AppetizerItem;
import edu.rpi.pagr.service.NotificationService;

public class AppetizerListActivity extends SherlockFragmentActivity {

    private Button button_submit_order;

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_appetizer_list);

        button_submit_order = (Button) findViewById(R.id.button_submit_order);

        ArrayList image_details = getListData();
        final ListView lv1 = (ListView) findViewById(R.id.appetizer_list);
        lv1.setAdapter(new CustomListAdapter(this, image_details));
        lv1.setOnItemClickListener(new OnItemClickListener() {

            @Override
            public void onItemClick(AdapterView<?> a, View v, int position, long id) {
                Object o = lv1.getItemAtPosition(position);
                AppetizerItem foodData = (AppetizerItem) o;
                Toast.makeText(AppetizerListActivity.this, "Selected :" + " " + foodData.getName(),
                        Toast.LENGTH_SHORT).show();
            }

        });

        button_submit_order.setOnClickListener( new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Toast.makeText(getBaseContext(), "We got your order!", Toast.LENGTH_SHORT).show();

                Intent serviceIntent = new Intent(getBaseContext(), NotificationService.class);
                startService(serviceIntent);
            }
        });
    }

    private ArrayList getListData() {
        ArrayList results = new ArrayList();
        AppetizerItem foodData = new AppetizerItem();
        foodData.setName("Beef Samosa");
        foodData.setImageURL("http://danielatwork.com/sdd/appetizers_and_drinks/beef_samosas.jpg");
        results.add(foodData);

        foodData = new AppetizerItem();
        foodData.setName("Bubble Tea");
        foodData.setImageURL("http://danielatwork.com/sdd/appetizers_and_drinks/drinks/bubble_tea.jpg");
        results.add(foodData);

        foodData = new AppetizerItem();
        foodData.setName("Empanada con Pollo");
        foodData.setImageURL("http://danielatwork.com/sdd/appetizers_and_drinks/empanada_con_pollo.jpg");
        results.add(foodData);

        return results;
    }
    
}
