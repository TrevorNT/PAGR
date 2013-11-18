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

import edu.rpi.pagr.GuestNumbersActivity;
import edu.rpi.pagr.MakeReservationActivity;

import static org.hamcrest.CoreMatchers.equalTo;
import static org.junit.Assert.assertNotNull;
import static org.junit.Assert.assertThat;
import static org.robolectric.Robolectric.clickOn;
import static org.robolectric.Robolectric.shadowOf;

import static org.fest.assertions.api.ANDROID.assertThat;

@RunWith(RobolectricGradleTestRunner.class)
public class GuestNumbersActivityTest {
    private GuestNumbersActivity activity;
    private Button pressButton;

    @Before
    public void setUp() throws Exception {
        activity = Robolectric.buildActivity(GuestNumbersActivity.class).create().visible().get();
        pressButton = (Button) activity.findViewById(R.id.button_ok);
    }

    @Test
    public void shouldHaveAButtonThatSaysOK() throws Exception {
        assertThat((String) pressMeButton.getText(), equalTo("OK"));
    }

    @Test
    public void pressingTheButtonShouldStartMakeReservationActivity() throws Exception {
        pressMeButton.performClick();

        ShadowActivity shadowActivity = shadowOf(activity);
        Intent startedIntent = shadowActivity.getNextStartedActivity();
        ShadowIntent shadowIntent = shadowOf(startedIntent);
        assertThat(shadowIntent.getComponent().getClassName(), equalTo(MakeReservationActivity.class.getName()));
    }
}