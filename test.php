                        <template x-if="isCombined">
                            <!-- Showing Combined Analytics -->
                            <div>
                                <div class="analtyicsStatFilter row">
                                    <!-- Views -->
                                    <div class="analyticsStatCard col-3" @click="activeTab = 'tabViews'" :class="{ 'activeCard': activeTab === 'tabViews' }">
                                        <div class="statLabel">
                                            <div class="iconLabel"><img src="/wp-content/uploads/2025/02/views.svg" alt=""></div>
                                            <h4>Views</h4>
                                        </div>
                                        <div class="statInform">
                                            <div class="statIndicate">
                                                <h3>300k</h3>
                                                <div class="icon"><img src="/wp-content/uploads/2025/07/lessthanusual.svg" alt=""></div>
                                            </div>
                                            <p>About the same as usual</p>
                                        </div>
                                    </div>
                                    <!-- Estimated Revenue -->
                                    <div class="analyticsStatCard col-3" @click="activeTab = 'tabRevenue'" :class="{ 'activeCard': activeTab === 'tabRevenue' }">
                                        <div class="statLabel">
                                            <div class="iconLabel"><img src="/wp-content/uploads/2025/02/total-revenue.svg" alt=""></div>
                                            <h4>Estimated Revenue</h4>
                                        </div>
                                        <div class="statInform">
                                            <div class="statIndicate">
                                                <h3>$50,739.19</h3>
                                                <div class="icon"><img src="/wp-content/uploads/2025/07/lessthanusual.svg" alt=""></div>
                                            </div>
                                            <p>About the same as usual</p>
                                        </div>
                                    </div>
                                    <!-- Expenses -->
                                    <div class="analyticsStatCard col-3" @click="activeTab = 'tabExpenses'" :class="{ 'activeCard': activeTab === 'tabExpenses' }">
                                        <div class="statLabel">
                                            <div class="iconLabel"><img src="/wp-content/uploads/2025/02/total-expenses.svg" alt=""></div>
                                            <h4>Expenses</h4>
                                        </div>
                                        <div class="statInform">
                                            <div class="statIndicate">
                                                <h3>$21,938.58</h3>
                                                <div class="icon"><img src="/wp-content/uploads/2025/07/lessthanusual.svg" alt=""></div>
                                            </div>
                                            <p>About the same as usual</p>
                                        </div>
                                    </div>
                                    <!-- Gross Profit -->
                                    <div class="analyticsStatCard col-3" @click="activeTab = 'tabGross'" :class="{ 'activeCard': activeTab === 'tabGross' }">
                                        <div class="statLabel">
                                            <div class="iconLabel"><img src="/wp-content/uploads/2025/08/grossprof.svg" alt=""></div>
                                            <h4>Gross Profit</h4>
                                        </div>
                                        <div class="statInform">
                                            <div class="statIndicate">
                                                <h3>$28,800.61</h3>
                                                <div class="icon"><img src="/wp-content/uploads/2025/07/lessthanusual.svg" alt=""></div>
                                            </div>
                                            <p>About the same as usual</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="statGraph">
                                    <div x-show="activeTab === 'tabViews'">
                                        <div class="col-12">
                                            <canvas id="combinedChartViews"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div x-show="activeTab === 'tabRevenue'">
                                <h3>Estimated Revenue</h3>
                            </div>
                            <div x-show="activeTab === 'tabExpenses'">
                                <h3>Expenses</h3>
                            </div>
                            <div x-show="activeTab === 'tabGross'">
                                <h3>Gross Profit</h3>
                            </div>
                        </template>
                        <template x-if="!isCombined">
                            <!-- Showing stats for Individual Channels -->
                            <div>
                                <div class="analtyicsStatFilter row">
                                    <!-- Views -->
                                    <div class="analyticsStatCard col-3" @click="activeTab = 'tabViews'" :class="{ 'activeCard': activeTab === 'tabViews' }">
                                        <div class="statLabel">
                                            <div class="iconLabel"><img src="/wp-content/uploads/2025/02/views.svg" alt=""></div>
                                            <h4>Views</h4>
                                        </div>
                                        <div class="statInform">
                                            <div class="statIndicate">
                                                <h3>300k</h3>
                                                <div class="icon"><img src="/wp-content/uploads/2025/07/lessthanusual.svg" alt=""></div>
                                            </div>
                                            <p>About the same as usual</p>
                                        </div>
                                    </div>
                                    <!-- Estimated Revenue -->
                                    <div class="analyticsStatCard col-3" @click="activeTab = 'tabRevenue'" :class="{ 'activeCard': activeTab === 'tabRevenue' }">
                                        <div class="statLabel">
                                            <div class="iconLabel"><img src="/wp-content/uploads/2025/02/total-revenue.svg" alt=""></div>
                                            <h4>Estimated Revenue</h4>
                                        </div>
                                        <div class="statInform">
                                            <div class="statIndicate">
                                                <h3>$50,739.19</h3>
                                                <div class="icon"><img src="/wp-content/uploads/2025/07/lessthanusual.svg" alt=""></div>
                                            </div>
                                            <p>About the same as usual</p>
                                        </div>
                                    </div>
                                    <!-- Expenses -->
                                    <div class="analyticsStatCard col-3" @click="activeTab = 'tabExpenses'" :class="{ 'activeCard': activeTab === 'tabExpenses' }">
                                        <div class="statLabel">
                                            <div class="iconLabel"><img src="/wp-content/uploads/2025/02/total-expenses.svg" alt=""></div>
                                            <h4>Expenses</h4>
                                        </div>
                                        <div class="statInform">
                                            <div class="statIndicate">
                                                <h3>$21,938.58</h3>
                                                <div class="icon"><img src="/wp-content/uploads/2025/07/lessthanusual.svg" alt=""></div>
                                            </div>
                                            <p>About the same as usual</p>
                                        </div>
                                    </div>
                                    <!-- Gross Profit -->
                                    <div class="analyticsStatCard col-3" @click="activeTab = 'tabGross'" :class="{ 'activeCard': activeTab === 'tabGross' }">
                                        <div class="statLabel">
                                            <div class="iconLabel"><img src="/wp-content/uploads/2025/08/grossprof.svg" alt=""></div>
                                            <h4>Gross Profit</h4>
                                        </div>
                                        <div class="statInform">
                                            <div class="statIndicate">
                                                <h3>$28,800.61</h3>
                                                <div class="icon"><img src="/wp-content/uploads/2025/07/lessthanusual.svg" alt=""></div>
                                            </div>
                                            <p>About the same as usual</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="statGraph">
                                    <div x-show="activeTab === 'tabViews'">
                                        <h3>Views</h3>

                                    </div>
                                    <div x-show="activeTab === 'tabRevenue'">
                                        <h3>Estimated Revenue</h3>
                                    </div>
                                    <div x-show="activeTab === 'tabExpenses'">
                                        <h3>Expenses</h3>
                                    </div>
                                    <div x-show="activeTab === 'tabGross'">
                                        <h3>Gross Profit</h3>
                                    </div>
                                    <div class="col-12">
                                        <canvas id="individualChartViews"></canvas>
                                    </div>
                                </div>
                                <div class="channelDisplay">
                                    <button class="channeldDisplayBtn"><img src="/wp-content/uploads/2025/07/channelbtnfallback.svg" alt="">
                                        Channel 1</button>
                                    <button class="channeldDisplayBtn"><img src="/wp-content/uploads/2025/07/channelbtnfallback.svg" alt="">
                                        Channel 2</button>
                                    <button class="channeldDisplayBtn"><img src="/wp-content/uploads/2025/07/channelbtnfallback.svg" alt="">
                                        Channel 3</button>
                                </div>
                            </div>
                        </template>