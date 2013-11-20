package com.example;

import android.content.Intent;
import android.view.View;
import android.widget.Button;
import android.widget.ImageView;
import com.pivotallabs.injected.InjectedActivity;
import com.pivotallabs.tracker.RecentActivityActivity;
import org.junit.Before;
import org.junit.Test;
import org.junit.runner.RunWith;
import org.robolectric.Robolectric;
import org.robolectric.shadows.ShadowActivity;
import org.robolectric.shadows.ShadowIntent;

import edu.rpi.pagr.AppetizerFragmentActivity;
import edu.rpi.pagr.GuestNumbersActivity;
import edu.rpi.pagr.MakeReservationActivity;

import static org.hamcrest.CoreMatchers.equalTo;
import static org.junit.Assert.assertNotNull;
import static org.junit.Assert.assertThat;
import static org.robolectric.Robolectric.clickOn;
import static org.robolectric.Robolectric.shadowOf;

import static org.fest.assertions.api.ANDROID.assertThat;

@RunWith(RobolectricGradleTestRunner.class)
public class MakeReservationActivityTest {
    private MakeReservationActivity activity;
    private Button pressButton;

    @Before
    public void setUp() throws Exception {
        activity = Robolectric.buildActivity(MakeReservationActivity.class).create().visible().get();
        pressButton = (Button) activity.findViewById(R.id.confirm_reservation);
    }

    @Test
    public void shouldHaveAButtonThatSaysOK() throws Exception {
        assertThat((String) pressMeButton.getText(), equalTo("Yes"));
    }

    @Test
    public void pressingTheButtonShouldStartAppetizerFragmentActivity() throws Exception {
        pressMeButton.performClick();

        ShadowActivity shadowActivity = shadowOf(activity);
        Intent startedIntent = shadowActivity.getNextStartedActivity();
        ShadowIntent shadowIntent = shadowOf(startedIntent);
        assertThat(shadowIntent.getComponent().getClassName(), equalTo(AppetizerFragmentActivity.class.getName()));
    }
}